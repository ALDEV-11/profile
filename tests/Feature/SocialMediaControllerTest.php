<?php

namespace Tests\Feature;

use App\Models\SocialMedia;
use App\Models\User;
use App\Services\SocialMediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialMediaControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $this->actingAs($user);

        return $user;
    }

    private function linkData(array $overrides = []): array
    {
        return array_merge([
            'user_id' => auth()->id(),
            'platform' => 'GitHub',
            'url' => 'https://github.com/username',
            'icon' => 'bx bxl-github',
        ], $overrides);
    }

    public function test_guest_is_redirected_from_social_media_index(): void
    {
        $this->get('/222/social-medias')->assertRedirect(route('login'));
    }

    public function test_index_lists_links(): void
    {
        $this->actingAsAdmin();

        SocialMedia::create($this->linkData(['url' => 'https://github.com/aldo']));

        $this->get(route('admin.social-medias.index'))
            ->assertOk()
            ->assertSee('GitHub')
            ->assertSee('https://github.com/aldo');
    }

    public function test_index_searches_by_platform(): void
    {
        $this->actingAsAdmin();

        SocialMedia::create($this->linkData(['url' => 'https://github.com/aldo']));
        SocialMedia::create($this->linkData(['platform' => 'LinkedIn', 'url' => 'https://linkedin.com/in/aldo', 'icon' => 'bx bxl-linkedin']));

        $this->get(route('admin.social-medias.index', ['q' => 'linkedin']))
            ->assertOk()
            ->assertSee('LinkedIn')
            ->assertDontSee('https://github.com/aldo');
    }

    public function test_create_page_renders(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.social-medias.create'))->assertOk();
    }

    public function test_store_validates_and_saves(): void
    {
        $user = $this->actingAsAdmin();

        $this->post(route('admin.social-medias.store'), $this->linkData())
            ->assertRedirect(route('admin.social-medias.index'));

        $this->assertDatabaseHas('social_medias', [
            'user_id' => $user->id,
            'platform' => 'GitHub',
            'url' => 'https://github.com/username',
            'icon' => 'bx bxl-github',
        ]);
    }

    public function test_store_auto_fills_icon_from_platform(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.social-medias.store'), $this->linkData(['icon' => null]))
            ->assertRedirect(route('admin.social-medias.index'));

        $this->assertDatabaseHas('social_medias', [
            'platform' => 'GitHub',
            'icon' => 'bx bxl-github',
        ]);
    }

    public function test_store_rejects_missing_fields(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.social-medias.store'), [])
            ->assertSessionHasErrors(['platform', 'url']);
    }

    public function test_store_rejects_invalid_url(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.social-medias.store'), $this->linkData(['url' => 'not-a-url']))
            ->assertSessionHasErrors('url');
    }

    public function test_store_rejects_url_without_scheme(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.social-medias.store'), $this->linkData(['url' => 'github.com/username']))
            ->assertSessionHasErrors('url');
    }

    public function test_store_rejects_duplicate_platform(): void
    {
        $user = $this->actingAsAdmin();

        SocialMedia::create($this->linkData() + ['user_id' => $user->id]);

        $this->post(route('admin.social-medias.store'), $this->linkData())
            ->assertSessionHasErrors('platform');

        $this->assertDatabaseCount('social_medias', 1);
    }

    public function test_store_rejects_when_max_five_links_reached(): void
    {
        $user = $this->actingAsAdmin();

        foreach (['GitHub', 'Instagram', 'LinkedIn', 'Twitter/X', 'Portfolio'] as $i => $platform) {
            SocialMedia::create([
                'user_id' => $user->id,
                'platform' => $platform,
                'url' => 'https://example.com/'.$i,
                'icon' => 'bx bx-link',
                'order' => $i + 1,
            ]);
        }

        $this->post(route('admin.social-medias.store'), [
            'platform' => 'Custom',
            'url' => 'https://example.com/custom',
        ])->assertSessionHasErrors('platform');

        $this->assertDatabaseCount('social_medias', SocialMediaService::MAX_LINKS);
    }

    public function test_edit_page_is_prefilled(): void
    {
        $this->actingAsAdmin();

        $link = SocialMedia::create($this->linkData());

        $this->get(route('admin.social-medias.edit', $link))
            ->assertOk()
            ->assertSee('GitHub');
    }

    public function test_update_validates_and_updates(): void
    {
        $this->actingAsAdmin();

        $link = SocialMedia::create($this->linkData());

        $this->put(route('admin.social-medias.update', $link), $this->linkData([
            'platform' => 'LinkedIn',
            'url' => 'https://linkedin.com/in/aldo',
            'icon' => 'bx bxl-linkedin',
        ]))->assertRedirect(route('admin.social-medias.index'));

        $this->assertDatabaseHas('social_medias', [
            'id' => $link->id,
            'platform' => 'LinkedIn',
            'url' => 'https://linkedin.com/in/aldo',
            'icon' => 'bx bxl-linkedin',
        ]);
    }

    public function test_destroy_deletes_link(): void
    {
        $this->actingAsAdmin();

        $link = SocialMedia::create($this->linkData());

        $this->delete(route('admin.social-medias.destroy', $link))
            ->assertRedirect(route('admin.social-medias.index'));

        $this->assertDatabaseCount('social_medias', 0);
    }

    public function test_store_defaults_order_when_not_provided(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.social-medias.store'), $this->linkData())
            ->assertRedirect(route('admin.social-medias.index'));

        $this->assertDatabaseHas('social_medias', [
            'platform' => 'GitHub',
            'order' => 1,
        ]);
    }

    public function test_store_saves_explicit_order(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.social-medias.store'), $this->linkData(['order' => 3]))
            ->assertRedirect(route('admin.social-medias.index'));

        $this->assertDatabaseHas('social_medias', [
            'platform' => 'GitHub',
            'order' => 3,
        ]);
    }

    public function test_update_saves_new_order(): void
    {
        $this->actingAsAdmin();

        $link = SocialMedia::create($this->linkData());

        $this->put(route('admin.social-medias.update', $link), $this->linkData(['order' => 2]))
            ->assertRedirect(route('admin.social-medias.index'));

        $this->assertDatabaseHas('social_medias', [
            'id' => $link->id,
            'order' => 2,
        ]);
    }

    public function test_store_rejects_negative_order(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.social-medias.store'), $this->linkData(['order' => -1]))
            ->assertSessionHasErrors('order');
    }
}
