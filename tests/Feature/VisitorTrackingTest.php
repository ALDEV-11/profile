<?php

namespace Tests\Feature;

use App\Models\Visitor;
use App\Repositories\VisitorRepository;
use App\Services\DeviceDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class VisitorTrackingTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // DeviceDetector
    // ------------------------------------------------------------------

    public function test_detects_desktop_from_user_agent(): void
    {
        $detector = new DeviceDetector();
        $this->assertSame('desktop', $detector->detect('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36')->value);
    }

    public function test_detects_mobile_from_user_agent(): void
    {
        $detector = new DeviceDetector();
        $this->assertSame('mobile', $detector->detect('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1')->value);
    }

    public function test_detects_tablet_from_user_agent(): void
    {
        $detector = new DeviceDetector();
        $this->assertSame('tablet', $detector->detect('Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1')->value);
    }

    public function test_detects_modern_ipad_macos_user_agent_as_tablet(): void
    {
        $detector = new DeviceDetector();
        $this->assertSame('tablet', $detector->detect('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1')->value);
    }

    // ------------------------------------------------------------------
    // VisitorRepository
    // ------------------------------------------------------------------

    public function test_repository_records_a_visit(): void
    {
        $request = Request::create('/projects', 'GET', server: [
            'REMOTE_ADDR' => '203.0.113.5',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 13) Mobile',
            'HTTP_REFERER' => 'https://google.com',
        ]);

        $result = app(VisitorRepository::class)->record($request);

        $this->assertTrue($result);
        $this->assertDatabaseHas('visitors', [
            'ip_address' => '203.0.113.5',
            'page_visited' => 'projects',
            'referer' => 'https://google.com',
            'device_type' => 'mobile',
        ]);
    }

    public function test_repository_never_crashes_when_insert_fails(): void
    {
        $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '203.0.113.7']);

        Visitor::creating(function () {
            throw new \RuntimeException('simulated database failure');
        });

        $result = app(VisitorRepository::class)->record($request);

        $this->assertFalse($result);
        $this->assertDatabaseCount('visitors', 0);
    }

    public function test_repository_counts_and_prunes(): void
    {
        Visitor::factory()->create(['device_type' => 'desktop', 'visited_at' => now()]);
        Visitor::factory()->create(['device_type' => 'desktop', 'visited_at' => now()]);
        Visitor::factory()->create(['device_type' => 'mobile', 'visited_at' => now()]);
        Visitor::factory()->count(2)->create(['device_type' => 'tablet', 'visited_at' => now()->subDays(100)]);

        $repo = app(VisitorRepository::class);

        $this->assertSame(5, $repo->total());
        $this->assertSame(3, $repo->countForDay(now()->toDateString()));
        $this->assertSame(2, $repo->countsByDevice()['desktop']);
        $this->assertSame(1, $repo->countsByDevice()['mobile']);
        $this->assertSame(2, $repo->countsByDevice()['tablet']);

        $deleted = $repo->prune(30);
        $this->assertSame(2, $deleted);
        $this->assertSame(3, $repo->total());
    }

    public function test_top_pages_returns_most_visited_first(): void
    {
        Visitor::factory()->create(['page_visited' => '/', 'visited_at' => now()]);
        Visitor::factory()->create(['page_visited' => '/', 'visited_at' => now()]);
        Visitor::factory()->create(['page_visited' => '/about', 'visited_at' => now()]);

        $top = app(VisitorRepository::class)->topPages(5);

        $this->assertSame('/', $top[0]['page_visited']);
        $this->assertSame(2, $top[0]['count']);
    }

    // ------------------------------------------------------------------
    // Cleanup command
    // ------------------------------------------------------------------

    public function test_cleanup_command_deletes_old_records(): void
    {
        Visitor::factory()->create(['visited_at' => now()->subDays(100)]);
        Visitor::factory()->create(['visited_at' => now()]);

        $this->artisan('visitors:cleanup', ['--days' => 30])
            ->expectsOutputToContain('Deleted 1 visitor record(s)')
            ->assertSuccessful();

        $this->assertDatabaseCount('visitors', 1);
    }

    public function test_cleanup_command_rejects_invalid_days(): void
    {
        $this->artisan('visitors:cleanup', ['--days' => 0])
            ->expectsOutputToContain('must be at least 1')
            ->assertExitCode(1);
    }
}
