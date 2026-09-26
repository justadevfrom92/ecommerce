<?php

namespace Tests\Feature;

use App\Mail\StoreMail;
use App\Models\Campaign;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\InboundMessage;
use App\Models\Order;
use App\Models\Subscriber;
use App\Models\User;
use App\Services\Emailer;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EmailTemplateSeeder::class);
    }

    public function test_sign_up_sends_welcome_and_can_subscribe(): void
    {
        Mail::fake();
        $this->seedPermissions();

        $this->post('/register', [
            'name' => 'Jamie', 'email' => 'jamie@example.com', 'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123', 'terms' => '1', 'newsletter' => '1',
        ]);

        Mail::assertSent(StoreMail::class, fn (StoreMail $m) => $m->hasTo('jamie@example.com') && $m->templateKey === 'welcome'
            && $m->mailSubject === 'Welcome to MyStore, Jamie!');
        $this->assertDatabaseHas('subscribers', ['email' => 'jamie@example.com', 'status' => 'subscribed', 'source' => 'signup']);
    }

    public function test_edited_template_wording_is_used(): void
    {
        Mail::fake();
        EmailTemplate::where('key', 'order_confirmation')->update(['subject' => 'Yay! {{order_number}} is in']);
        $order = Order::factory()->withItems()->create(['status' => 'pending_payment']);

        $order->markPaid('pi_x');

        Mail::assertSent(StoreMail::class, fn (StoreMail $m) => $m->mailSubject === "Yay! {$order->number} is in"
            && str_contains($m->body, $order->items->first()->name));
    }

    public function test_status_change_emails_customer_only_for_notable_statuses(): void
    {
        Mail::fake();
        $order = Order::factory()->create(['status' => 'paid']);

        $order->transitionTo('processing');
        Mail::assertNothingSent();

        $order->transitionTo('shipped');
        Mail::assertSent(StoreMail::class, fn (StoreMail $m) => $m->templateKey === 'order_status' && str_contains($m->mailSubject, 'Shipped'));
    }

    public function test_password_reset_uses_template(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

        Mail::assertSent(StoreMail::class, fn (StoreMail $m) => $m->templateKey === 'password_reset' && str_contains($m->buttonUrl, '/reset-password/'));
    }

    public function test_sent_email_is_logged_once_with_template_key(): void
    {
        config(['mail.default' => 'array']);

        app(Emailer::class)->sendTemplate('pat@example.com', 'welcome', ['name' => 'Pat'], route('home'));

        $this->assertSame(1, EmailLog::count());
        $log = EmailLog::first();
        $this->assertSame('pat@example.com', $log->to);
        $this->assertSame('welcome', $log->template_key);
        $this->assertSame('sent', $log->status);
        $this->assertNotNull($log->message_id);
    }

    public function test_failed_send_is_logged(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 1]);

        $ok = app(Emailer::class)->sendTemplate('pat@example.com', 'welcome', ['name' => 'Pat']);

        $this->assertFalse($ok);
        $this->assertDatabaseHas('email_logs', ['to' => 'pat@example.com', 'status' => 'failed']);
    }

    public function test_mailgun_event_webhook_updates_log_and_unsubscribes_complaints(): void
    {
        config(['services.mailgun.webhook_signing_key' => 'key-test']);
        $log = EmailLog::create(['to' => 'a@example.com', 'subject' => 'Hi', 'message_id' => 'abc123@mystore.test', 'status' => 'sent']);
        Subscriber::subscribe('a@example.com');

        $payload = fn ($event, $sig = null) => [
            'signature' => $this->mailgunSignature('key-test', $sig),
            'event-data' => ['event' => $event, 'recipient' => 'a@example.com', 'message' => ['headers' => ['message-id' => 'abc123@mystore.test']]],
        ];

        $this->postJson('/webhooks/mailgun/events', $payload('delivered', 'bad'))->assertStatus(406);
        $this->assertSame('sent', $log->fresh()->status);

        $this->postJson('/webhooks/mailgun/events', $payload('delivered'))->assertOk();
        $this->assertSame('delivered', $log->fresh()->status);

        $this->postJson('/webhooks/mailgun/events', $payload('complained'))->assertOk();
        $this->assertSame('complained', $log->fresh()->status);
        $this->assertSame('unsubscribed', Subscriber::first()->status);
    }

    public function test_mailgun_inbound_creates_inbox_message(): void
    {
        config(['services.mailgun.webhook_signing_key' => 'key-test']);
        $customer = User::factory()->create(['email' => 'jane@example.com']);

        $this->post('/webhooks/mailgun/inbound', [
            ...$this->mailgunSignature('key-test'),
            'from' => 'Jane Doe <Jane@Example.com>',
            'subject' => 'Where is my parcel?',
            'body-plain' => "Hello\n\n> quoted",
            'stripped-text' => 'Hello',
        ])->assertOk();

        $message = InboundMessage::first();
        $this->assertSame('jane@example.com', $message->from_email);
        $this->assertSame('Jane Doe', $message->from_name);
        $this->assertSame('Hello', $message->body);
        $this->assertSame($customer->id, $message->user_id);

        $this->post('/webhooks/mailgun/inbound', ['from' => 'x@example.com', 'timestamp' => time(), 'token' => 't', 'signature' => 'nope'])->assertStatus(406);
        $this->assertSame(1, InboundMessage::count());
    }

    public function test_contact_form_and_honeypot(): void
    {
        $this->post('/contact', ['name' => 'Kim', 'email' => 'kim@example.com', 'subject' => 'Hi', 'message' => 'Hello there'])->assertSessionHas('status');
        $this->post('/contact', ['name' => 'Bot', 'email' => 'bot@example.com', 'subject' => 'Buy', 'message' => 'spam', 'website' => 'http://spam'])->assertSessionHas('status');

        $this->assertSame(1, InboundMessage::count());
        $this->assertSame('contact', InboundMessage::first()->source);
    }

    public function test_admin_reads_and_replies_to_inbox(): void
    {
        Mail::fake();
        $admin = $this->superAdmin();
        $message = InboundMessage::create(['from_email' => 'kim@example.com', 'subject' => 'Sizing', 'body' => 'Is M big?']);

        $this->actingAs($admin)->get('/admin/emails/inbox')->assertOk()->assertSee('Sizing');
        $this->actingAs($admin)->get("/admin/emails/inbox/{$message->id}")->assertOk()->assertSee('Is M big?');
        $this->assertNotNull($message->fresh()->read_at);

        $this->actingAs($admin)->post("/admin/emails/inbox/{$message->id}/reply", ['body' => 'It runs large.'])->assertSessionHas('status');

        Mail::assertSent(StoreMail::class, fn (StoreMail $m) => $m->hasTo('kim@example.com') && $m->mailSubject === 'Re: Sizing' && str_contains($m->body, 'It runs large.'));
        $this->assertNotNull($message->fresh()->replied_at);
        $this->assertSame(1, $message->replies()->count());
    }

    public function test_newsletter_signup_and_signed_unsubscribe(): void
    {
        Mail::fake();

        $this->post('/newsletter', ['email' => 'Fan@Example.com'])->assertSessionHas('status');
        $subscriber = Subscriber::firstOrFail();
        $this->assertSame('fan@example.com', $subscriber->email);
        Mail::assertSent(StoreMail::class, fn (StoreMail $m) => $m->templateKey === 'newsletter_welcome' && $m->unsubscribeUrl !== null);

        $this->get("/newsletter/unsubscribe/{$subscriber->id}")->assertForbidden(); // unsigned
        $this->get($subscriber->unsubscribeUrl())->assertOk()->assertSee('unsubscribed');
        $this->assertSame('unsubscribed', $subscriber->fresh()->status);
    }

    public function test_campaign_sends_to_active_subscribers_once(): void
    {
        Mail::fake();
        $admin = $this->superAdmin();
        Subscriber::subscribe('one@example.com', 'One');
        Subscriber::subscribe('two@example.com');
        Subscriber::subscribe('gone@example.com');
        Subscriber::where('email', 'gone@example.com')->first()->unsubscribe();

        $this->actingAs($admin)->post('/admin/emails/campaigns', ['subject' => 'Summer sale', 'body' => 'Hi {{name}}, 20% off!'])->assertSessionHasNoErrors();
        $campaign = Campaign::firstOrFail();

        $this->actingAs($admin)->get("/admin/emails/campaigns/{$campaign->id}/edit")->assertOk()->assertSee('Send to 2 subscribers');

        $this->actingAs($admin)->post("/admin/emails/campaigns/{$campaign->id}/send")->assertSessionHas('status');
        $this->actingAs($admin)->post("/admin/emails/campaigns/{$campaign->id}/send")->assertSessionHas('error');

        Mail::assertSent(StoreMail::class, 2);
        Mail::assertSent(StoreMail::class, fn (StoreMail $m) => $m->hasTo('one@example.com') && str_contains($m->body, 'Hi One, 20% off!') && $m->unsubscribeUrl);
        Mail::assertNotSent(StoreMail::class, fn (StoreMail $m) => $m->hasTo('gone@example.com'));

        $campaign->refresh();
        $this->assertSame('sent', $campaign->status);
        $this->assertSame(2, $campaign->sent_count);

        $this->actingAs($admin)->put("/admin/emails/campaigns/{$campaign->id}", ['subject' => 'Edit', 'body' => 'x'])->assertForbidden();
    }

    public function test_email_admin_pages_render_and_are_permission_gated(): void
    {
        $admin = $this->superAdmin();
        $message = InboundMessage::create(['from_email' => 'x@example.com', 'body' => 'Hi']);
        $campaign = Campaign::create(['subject' => 'S', 'body' => 'B']);

        foreach (['/admin/emails/inbox', "/admin/emails/inbox/{$message->id}", '/admin/emails/log', '/admin/emails/templates',
            '/admin/emails/templates/welcome/edit', '/admin/emails/subscribers', '/admin/emails/subscribers/export',
            '/admin/emails/campaigns', '/admin/emails/campaigns/create', "/admin/emails/campaigns/{$campaign->id}/edit"] as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }

        $support = $this->userWithPermissions(['admin.access', 'emails.inbox']);
        $this->actingAs($support)->get('/admin/emails/inbox')->assertOk();
        $this->actingAs($support)->get('/admin/emails/campaigns')->assertForbidden();
        $this->actingAs($support)->get('/admin/emails/templates')->assertForbidden();
    }

    public function test_template_edit_and_reset(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put('/admin/emails/templates/welcome', ['subject' => 'Hello {{name}}', 'body' => 'Custom body'])->assertSessionHasNoErrors();
        $this->assertSame('Custom body', EmailTemplate::firstWhere('key', 'welcome')->body);

        $this->actingAs($admin)->post('/admin/emails/templates/welcome/reset');
        $this->assertSame(EmailTemplate::DEFAULTS['welcome']['body'], EmailTemplate::firstWhere('key', 'welcome')->body);
    }

    private function mailgunSignature(string $key, ?string $override = null): array
    {
        $timestamp = (string) time();
        $token = bin2hex(random_bytes(10));

        return ['timestamp' => $timestamp, 'token' => $token, 'signature' => $override ?? hash_hmac('sha256', $timestamp.$token, $key)];
    }
}
