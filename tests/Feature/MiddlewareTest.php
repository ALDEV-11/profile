<?php

namespace Tests\Feature;

use App\Models\AuthSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // TrackVisitors
    // ------------------------------------------------------------------

    public function test_public_page_records_an_anonymous_visitor(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile')
            ->get('/');

        $this->assertDatabaseHas('visitors', [
            'page_visited' => '/',
            'ip_address' => '8.8.8.8',
            'device_type' => 'mobile',
        ]);
    }

    public function test_tablet_user_agent_is_classified_as_tablet(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15')
            ->get('/');

        $this->assertDatabaseHas('visitors', ['device_type' => 'tablet']);
    }

    public function test_desktop_user_agent_is_classified_as_desktop(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36')
            ->get('/');

        $this->assertDatabaseHas('visitors', ['device_type' => 'desktop']);
    }

    public function test_authenticated_users_are_not_tracked(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/');

        $this->assertDatabaseCount('visitors', 0);
    }

    public function test_admin_pages_are_not_tracked(): void
    {
        $this->get('/222/auth/login');

        $this->assertDatabaseCount('visitors', 0);
    }

    // ------------------------------------------------------------------
    // CheckAdminAccess
    // ------------------------------------------------------------------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/222/dashboard')->assertRedirect(route('login'));
    }

    public function test_soft_deleted_admin_is_logged_out(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $this->actingAs($user)->get('/222/dashboard')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_section_is_rate_limited(): void
    {
        $user = User::factory()->create();
        $key = 'admin:'.$user->id.':127.0.0.1';

        RateLimiter::clear($key);
        for ($i = 0; $i < 60; $i++) {
            RateLimiter::hit($key, 60);
        }

        $this->actingAs($user)->get('/222/dashboard')->assertStatus(429);
    }

    // ------------------------------------------------------------------
    // VerifyTwoFactorAuth
    // ------------------------------------------------------------------

    public function test_user_with_2fa_enabled_is_redirected_to_otp_page(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => true]);

        $this->actingAs($user)->get('/222/dashboard')->assertRedirect(route('two-factor.verify'));
    }

    public function test_user_with_verified_2fa_can_access_dashboard(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => true]);

        $this->withSession(['two_factor_authenticated' => true])
            ->actingAs($user)
            ->get('/222/dashboard')
            ->assertOk();
    }

    public function test_user_without_2fa_can_access_dashboard(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);

        $this->actingAs($user)->get('/222/dashboard')->assertOk();
    }

    // ------------------------------------------------------------------
    // NoStore
    // ------------------------------------------------------------------

    public function test_admin_pages_are_not_cached(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/222/dashboard')
            ->assertOk()
            ->assertHeader('Cache-Control', 'must-revalidate, no-cache, no-store, private')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');
    }

    public function test_otp_verification_page_is_reachable_when_2fa_pending(): void
    {
        $user = User::factory()->create(['two_factor_enabled' => true]);

        AuthSession::create([
            'user_id' => $user->id,
            'otp_hash' => Hash::make('1234'),
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
            'last_sent_at' => now(),
        ]);

        $this->actingAs($user)->get(route('two-factor.verify'))->assertOk();
    }
}
