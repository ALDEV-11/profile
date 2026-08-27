<?php

namespace Tests\Feature;

use App\Models\Hobby;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HobbyControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $this->actingAs($user);

        return $user;
    }

    public function test_guest_is_redirected_from_hobbies_index(): void
    {
        $this->get('/222/hobbies')->assertRedirect(route('login'));
    }

    public function test_index_lists_hobbies(): void
    {
        $this->actingAsAdmin();

        Hobby::create(['user_id' => auth()->id(), 'name' => 'Gaming', 'icon' => '🎮', 'order' => 2]);
        Hobby::create(['user_id' => auth()->id(), 'name' => 'Coding', 'icon' => '💻', 'order' => 1]);

        $this->get(route('admin.hobbies.index'))
            ->assertOk()
            ->assertSee('Coding')
            ->assertSee('Gaming');
    }

    public function test_create_page_renders(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.hobbies.create'))->assertOk();
    }

    public function test_store_validates_and_saves(): void
    {
        $user = $this->actingAsAdmin();

        $this->post(route('admin.hobbies.store'), [
            'name' => 'Photography',
            'icon' => '📸',
            'order' => 3,
        ])->assertRedirect(route('admin.hobbies.index'));

        $this->assertDatabaseHas('hobbies', [
            'user_id' => $user->id,
            'name' => 'Photography',
            'icon' => '📸',
            'order' => 3,
        ]);
    }

    public function test_store_rejects_missing_fields(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.hobbies.store'), [])
            ->assertSessionHasErrors(['name', 'icon']);

        $this->assertDatabaseCount('hobbies', 0);
    }

    public function test_edit_page_is_prefilled(): void
    {
        $this->actingAsAdmin();

        $hobby = Hobby::create(['user_id' => auth()->id(), 'name' => 'Music', 'icon' => '🎸', 'order' => 4]);

        $this->get(route('admin.hobbies.edit', $hobby))
            ->assertOk()
            ->assertSee('Music');
    }

    public function test_update_validates_and_updates(): void
    {
        $this->actingAsAdmin();

        $hobby = Hobby::create(['user_id' => auth()->id(), 'name' => 'Music', 'icon' => '🎸', 'order' => 4]);

        $this->put(route('admin.hobbies.update', $hobby), [
            'name' => 'Guitar',
            'icon' => '🎵',
            'order' => 1,
        ])->assertRedirect(route('admin.hobbies.index'));

        $this->assertDatabaseHas('hobbies', [
            'id' => $hobby->id,
            'name' => 'Guitar',
            'icon' => '🎵',
            'order' => 1,
        ]);
    }

    public function test_destroy_soft_deletes_hobby(): void
    {
        $this->actingAsAdmin();

        $hobby = Hobby::create(['user_id' => auth()->id(), 'name' => 'Gaming', 'icon' => '🎮', 'order' => 2]);

        $this->delete(route('admin.hobbies.destroy', $hobby))
            ->assertRedirect(route('admin.hobbies.index'));

        $this->assertSoftDeleted('hobbies', ['id' => $hobby->id]);
    }
}
