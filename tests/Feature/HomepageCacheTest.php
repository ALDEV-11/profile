<?php

namespace Tests\Feature;

use App\Enums\GalleryCategory;
use App\Models\Gallery;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_homepage_payload_contains_only_plain_arrays(): void
    {
        Profile::factory()->create(['full_name' => 'Cache Tester']);

        Gallery::create([
            'user_id' => User::factory()->create()->id,
            'image_path' => 'galleries/cache-test-large.jpg',
            'caption' => 'Cached shot',
            'category' => GalleryCategory::Project,
            'order' => 0,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Cache Tester');

        $cached = Cache::get('home.page.data');

        $this->assertIsArray($cached);
        $this->assertIsArray($cached['profile']);
        $this->assertIsArray($cached['socialMedias']);
        $this->assertIsArray($cached['hobbies']);
        $this->assertIsArray($cached['educations']);
        $this->assertIsArray($cached['galleries']);
        $this->assertIsArray($cached['galleries'][0]);
    }

    public function test_homepage_renders_from_the_cache_on_the_second_request(): void
    {
        Profile::factory()->create(['full_name' => 'Cache Tester']);

        $this->get('/')->assertOk()->assertSee('Cache Tester');
        $this->get('/')->assertOk()->assertSee('Cache Tester');
    }

    public function test_content_change_invalidates_the_homepage_cache(): void
    {
        $profile = Profile::factory()->create(['full_name' => 'Before Edit']);

        $this->get('/')->assertOk()->assertSee('Before Edit');

        $profile->update(['full_name' => 'After Edit']);

        $this->get('/')->assertOk()->assertSee('After Edit');
    }
}
