<?php

namespace Tests\Feature;

use App\Enums\DeviceType;
use App\Models\ContactMessage;
use App\Models\Gallery;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $this->actingAs($user);

        return $user;
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_renders_statistics(): void
    {
        $this->actingAsAdmin();

        Visitor::factory()->count(3)->create(['device_type' => DeviceType::Desktop]);
        ContactMessage::factory()->count(2)->create();
        ContactMessage::factory()->create(['is_read' => true]);
        Gallery::factory()->count(4)->create(['user_id' => auth()->id()]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('3')
            ->assertSee('5')
            ->assertSee('4');
    }

    public function test_dashboard_guest_cannot_read_unread_count(): void
    {
        $this->getJson(route('admin.dashboard.unread'))->assertUnauthorized();
    }

    public function test_unread_count_endpoint_returns_json(): void
    {
        $this->actingAsAdmin();

        ContactMessage::factory()->count(2)->create();
        ContactMessage::factory()->create(['is_read' => true]);

        $this->getJson(route('admin.dashboard.unread'))
            ->assertOk()
            ->assertJson(['unread' => 2]);
    }

    public function test_dashboard_shows_welcome_message_with_admin_name(): void
    {
        $user = $this->actingAsAdmin();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee($user->name);
    }

    public function test_dashboard_cache_stores_plain_arrays(): void
    {
        $this->actingAsAdmin();
        ContactMessage::factory()->count(2)->create();

        $this->get(route('dashboard'))->assertOk();

        $overview = Cache::get('admin.dashboard.overview');
        $recent = Cache::get('admin.messages.recent');

        $this->assertIsArray($overview);
        $this->assertIsArray($overview['recentMessages']);
        $this->assertIsArray($overview['recentMessages'][0]);
        $this->assertIsArray($recent);
        $this->assertIsArray($recent[0]);
    }

    public function test_dashboard_renders_from_the_cache_on_the_second_request(): void
    {
        $this->actingAsAdmin();

        Visitor::factory()->count(2)->create(['device_type' => DeviceType::Desktop]);
        ContactMessage::factory()->create();

        $this->get(route('dashboard'))->assertOk()->assertSee('2');
        $this->get(route('dashboard'))->assertOk()->assertSee('2');
    }
}
