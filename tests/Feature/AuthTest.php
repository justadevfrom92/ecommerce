<?php

namespace Tests\Feature;

use App\Mail\StoreMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_up_creates_a_customer_and_logs_in(): void
    {
        $this->seedPermissions();

        $this->post('/register', [
            'name' => 'Jamie Doe',
            'email' => 'jamie@example.com',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'terms' => '1',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $user = User::where('email', 'jamie@example.com')->first();
        $this->assertTrue($user->roles->contains('slug', 'customer'));
        $this->assertFalse($user->can('admin.access'));
    }

    public function test_sign_up_requires_terms_and_matching_passwords(): void
    {
        $this->post('/register', [
            'name' => 'Jamie', 'email' => 'jamie@example.com',
            'password' => 'secret-pass-123', 'password_confirmation' => 'nope',
        ])->assertSessionHasErrors(['password', 'terms']);

        $this->assertGuest();
    }

    public function test_login_popup_logs_in(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass-123'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_bad_login_goes_to_login_error_bag(): void
    {
        $user = User::factory()->create();

        $this->from('/')->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/')
            ->assertSessionHasErrorsIn('login', 'email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass-123', 'is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass-123']);

        $this->assertGuest();
    }

    public function test_deactivated_user_is_signed_out(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)->get('/account')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_account_menu_shows_login_form_for_guests(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('nav-password', false)
            ->assertSee('Create an account');
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account', ['name' => 'New Name', 'email' => 'new@example.com'])->assertSessionHasNoErrors();
        $this->assertSame('New Name', $user->fresh()->name);
    }

    public function test_password_is_changed_only_through_an_emailed_link(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => 'old-password-1']);

        // No password form on the account page any more.
        $this->actingAs($user)->put('/account/password', ['password' => 'x'])->assertNotFound();

        $this->actingAs($user)->post('/account/password-reset')->assertSessionHas('status');

        $url = null;
        Mail::assertSent(StoreMail::class, function (StoreMail $m) use ($user, &$url) {
            $url = $m->buttonUrl;

            return $m->hasTo($user->email) && $m->templateKey === 'password_reset';
        });

        // The emailed link works while signed in.
        $path = parse_url($url, PHP_URL_PATH);
        $this->actingAs($user)->get($path.'?email='.urlencode($user->email))->assertOk()->assertSee('Reset new password');

        $token = basename($path);
        $this->actingAs($user)->post('/reset-password', [
            'token' => $token, 'email' => $user->email, 'password' => 'brand-new-pass-1', 'password_confirmation' => 'brand-new-pass-1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('brand-new-pass-1', $user->fresh()->password));
    }
}
