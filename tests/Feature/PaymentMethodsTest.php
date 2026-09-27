<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tab_shows_not_enabled_without_stripe(): void
    {
        config(['services.stripe.secret' => null]);

        $this->actingAs(User::factory()->create())->get('/account/payments')
            ->assertOk()->assertSee('Payments')->assertSee('switched on for this store');
    }

    public function test_lists_saved_methods_with_default(): void
    {
        config(['services.stripe.secret' => 'sk_test_x']);
        $user = User::factory()->create(['stripe_customer_id' => 'cus_1']);

        Http::fake([
            'api.stripe.com/v1/customers/cus_1/payment_methods*' => Http::response(['data' => [
                ['id' => 'pm_visa', 'type' => 'card', 'card' => ['brand' => 'visa', 'last4' => '4242', 'exp_month' => 4, 'exp_year' => 2030]],
                ['id' => 'pm_mc', 'type' => 'card', 'card' => ['brand' => 'mastercard', 'last4' => '4444', 'exp_month' => 12, 'exp_year' => 2029]],
            ]]),
            'api.stripe.com/v1/customers/cus_1' => Http::response(['id' => 'cus_1', 'invoice_settings' => ['default_payment_method' => 'pm_mc']]),
        ]);

        $this->actingAs($user)->get('/account/payments')->assertOk()
            ->assertSeeInOrder(['Visa •••• 4242', 'Expires 04/2030', 'Mastercard •••• 4444', 'Default'])
            ->assertSee('Default payment');
    }

    public function test_add_creates_customer_and_redirects_to_stripe(): void
    {
        config(['services.stripe.secret' => 'sk_test_x']);
        $user = User::factory()->create();

        Http::fake([
            'api.stripe.com/v1/customers' => Http::response(['id' => 'cus_new']),
            'api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_setup', 'url' => 'https://checkout.stripe.com/setup/cs_setup']),
        ]);

        $this->actingAs($user)->post('/account/payments')->assertRedirect('https://checkout.stripe.com/setup/cs_setup');

        $this->assertSame('cus_new', $user->fresh()->stripe_customer_id);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/checkout/sessions') && $r['mode'] === 'setup' && $r['customer'] === 'cus_new');
    }

    public function test_set_default_only_for_own_methods(): void
    {
        config(['services.stripe.secret' => 'sk_test_x']);
        $user = User::factory()->create(['stripe_customer_id' => 'cus_1']);

        Http::fake([
            'api.stripe.com/v1/payment_methods/pm_mine' => Http::response(['id' => 'pm_mine', 'customer' => 'cus_1']),
            'api.stripe.com/v1/payment_methods/pm_theirs' => Http::response(['id' => 'pm_theirs', 'customer' => 'cus_2']),
            'api.stripe.com/v1/customers/cus_1' => Http::response(['id' => 'cus_1']),
            'api.stripe.com/v1/payment_methods/*/detach' => Http::response(['id' => 'x']),
        ]);

        $this->actingAs($user)->post('/account/payments/pm_mine/default')->assertSessionHas('status');
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/customers/cus_1') && $r->method() === 'POST'
            && $r['invoice_settings']['default_payment_method'] === 'pm_mine');

        $this->actingAs($user)->post('/account/payments/pm_theirs/default')->assertSessionHas('error');
        $this->actingAs($user)->delete('/account/payments/pm_theirs')->assertSessionHas('error');
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'pm_theirs/detach'));
    }
}
