<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\AuthSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_admin_can_login_and_reach_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'two_factor_enabled' => false,
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_invalid_credentials_redirect_back_with_error(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_soft_deleted_admin_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $user->delete();

        $this->post(route('login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_with_2fa_redirects_to_otp_page_and_sends_mail(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'two_factor_enabled' => true,
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect(route('two-factor.verify'));

        $session = AuthSession::activeFor($user->id);

        $this->assertNotNull($session);
        $this->assertTrue($session->expires_at->isFuture());

        Mail::assertSent(OtpMail::class);

        // Not yet verified → dashboard still blocked
        $this->get(route('dashboard'))->assertRedirect(route('two-factor.verify'));
    }

    public function test_otp_verification_grants_dashboard_access(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'two_factor_enabled' => true,
        ]);

        $this->post(route('login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $otp = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$otp) {
            $otp = $mail->otp;

            return true;
        });

        $this->post(route('two-factor.verify.post'), ['otp' => $otp])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('auth_sessions', 0);
        $this->assertTrue(session('two_factor_authenticated'));
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_wrong_otp_is_rejected(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post(route('two-factor.verify.post'), ['otp' => '0000'])
            ->assertSessionHasErrors('otp');

        $this->get(route('dashboard'))->assertRedirect(route('two-factor.verify'));
    }

    public function test_three_failed_otp_attempts_logs_the_user_out(): void
    {
        $user = User::factory()->create([
            'two_factor_enabled' => true,
        ]);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        foreach (range(1, 3) as $i) {
            $this->post(route('two-factor.verify.post'), ['otp' => '0000']);
        }

        $this->assertGuest();
    }
}
