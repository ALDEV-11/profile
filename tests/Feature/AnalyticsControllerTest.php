<?php

namespace Tests\Feature;

use App\Enums\DeviceType;
use App\Models\User;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $this->actingAs($user);

        return $user;
    }

    private function makeVisitor(array $overrides = []): Visitor
    {
        return Visitor::factory()->create($overrides);
    }

    public function test_guest_is_redirected_from_analytics(): void
    {
        $this->get('/222/analytics')->assertRedirect(route('login'));
    }

    public function test_index_renders_statistics_cards(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['ip_address' => '1.1.1.1', 'visited_at' => now()]);
        $this->makeVisitor(['ip_address' => '1.1.1.1', 'visited_at' => now()]);
        $this->makeVisitor(['ip_address' => '2.2.2.2', 'visited_at' => now()]);

        $this->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSee('Unique Visitors')
            ->assertSee('Page Views')
            ->assertSee('Pengunjung Hari Ini')
            ->assertSee('Visitor Trend')
            ->assertSee('Halaman Terpopuler');
    }

    public function test_data_endpoint_returns_combined_payload(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['ip_address' => '1.1.1.1', 'visited_at' => now()]);

        $this->getJson(route('admin.analytics.data'))
            ->assertOk()
            ->assertJsonStructure([
                'range',
                'cards' => ['visitors' => ['value', 'delta', 'trend'], 'pageViews' => ['value', 'delta', 'trend'], 'newToday' => ['value', 'delta', 'trend']],
                'trend' => ['labels', 'data'],
                'devices' => ['labels', 'data', 'colors'],
                'referrers' => ['labels', 'data'],
                'pages',
            ]);
    }

    public function test_range_filter_limits_data_to_seven_days(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['ip_address' => '1.1.1.1', 'visited_at' => now()->subDays(15)]);
        $this->makeVisitor(['ip_address' => '2.2.2.2', 'visited_at' => now()->subDays(2)]);

        $response = $this->getJson(route('admin.analytics.data', ['range' => '7d']))->json();

        $this->assertSame(1, $response['cards']['visitors']['value']);
        $this->assertSame(1, $response['cards']['pageViews']['value']);
    }

    public function test_all_time_range_includes_old_records(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['ip_address' => '1.1.1.1', 'visited_at' => now()->subDays(60)]);
        $this->makeVisitor(['ip_address' => '2.2.2.2', 'visited_at' => now()]);

        $response = $this->getJson(route('admin.analytics.data', ['range' => 'all']))->json();

        $this->assertSame(2, $response['cards']['visitors']['value']);
    }

    public function test_custom_range_is_respected(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['ip_address' => '1.1.1.1', 'visited_at' => Carbon::parse('2026-01-10')]);
        $this->makeVisitor(['ip_address' => '2.2.2.2', 'visited_at' => Carbon::parse('2026-01-20')]);

        $response = $this->getJson(route('admin.analytics.data', [
            'range' => 'custom',
            'start' => '2026-01-01',
            'end' => '2026-01-15',
        ]))->json();

        $this->assertSame(1, $response['cards']['visitors']['value']);
        $this->assertSame(1, $response['cards']['pageViews']['value']);
    }

    public function test_device_breakdown_groups_correctly(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['device_type' => DeviceType::Desktop]);
        $this->makeVisitor(['device_type' => DeviceType::Desktop]);
        $this->makeVisitor(['device_type' => DeviceType::Mobile]);
        $this->makeVisitor(['device_type' => DeviceType::Tablet]);

        $response = $this->getJson(route('admin.analytics.data'))->json();

        $this->assertSame(2, $response['devices']['data'][0]);
        $this->assertSame(1, $response['devices']['data'][1]);
        $this->assertSame(1, $response['devices']['data'][2]);
        $this->assertSame(['Desktop', 'Mobile', 'Tablet'], $response['devices']['labels']);
    }

    public function test_referrers_are_categorized(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['referer' => null]);
        $this->makeVisitor(['referer' => 'https://www.google.com/search?q=foo']);
        $this->makeVisitor(['referer' => 'https://www.facebook.com/user']);
        $this->makeVisitor(['referer' => 'https://blog.other-site.com/post']);

        $response = $this->getJson(route('admin.analytics.data'))->json();

        $labels = $response['referrers']['labels'];
        $data = $response['referrers']['data'];

        $this->assertContains('Direct', $labels);
        $this->assertContains('Google', $labels);
        $this->assertContains('Social Media', $labels);
        $this->assertContains('Referral', $labels);
        $this->assertSame(4, array_sum($data));
    }

    public function test_top_pages_sorted_by_views_with_unique(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['page_visited' => '/', 'ip_address' => '1.1.1.1']);
        $this->makeVisitor(['page_visited' => '/', 'ip_address' => '1.1.1.1']);
        $this->makeVisitor(['page_visited' => '/', 'ip_address' => '2.2.2.2']);
        $this->makeVisitor(['page_visited' => '/about', 'ip_address' => '1.1.1.1']);

        $response = $this->getJson(route('admin.analytics.data'))->json();

        $this->assertSame('/', $response['pages'][0]['page']);
        $this->assertSame(3, $response['pages'][0]['views']);
        $this->assertSame(2, $response['pages'][0]['unique']);
        $this->assertSame('/about', $response['pages'][1]['page']);
    }

    public function test_trend_data_is_daily_and_zero_filled(): void
    {
        $this->actingAsAdmin();

        $this->makeVisitor(['ip_address' => '1.1.1.1', 'visited_at' => now()->subDays(2)]);

        $response = $this->getJson(route('admin.analytics.data', ['range' => '7d']))->json();

        $this->assertCount(7, $response['trend']['labels']);
        $this->assertCount(7, $response['trend']['data']);
        $this->assertSame(1, array_sum($response['trend']['data']));
    }

    public function test_trend_groups_by_asia_jakarta_day(): void
    {
        $this->actingAsAdmin();

        // 18:00 UTC is already 01:00 the next day in Asia/Jakarta (UTC+7).
        $this->makeVisitor([
            'ip_address' => '1.1.1.1',
            'visited_at' => Carbon::parse('2026-01-10 18:00:00', 'UTC'),
        ]);

        $response = $this->getJson(route('admin.analytics.data', [
            'range' => 'custom',
            'start' => '2026-01-09',
            'end' => '2026-01-11',
        ]))->json();

        $labels = $response['trend']['labels'];
        $data = $response['trend']['data'];

        $jakartaIndex = array_search('11 Jan', $labels, true);

        $this->assertNotFalse($jakartaIndex);
        $this->assertSame(1, $data[$jakartaIndex]);
        $this->assertSame(0, $data[$jakartaIndex - 1]);
    }
}
