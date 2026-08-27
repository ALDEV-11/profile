<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $this->actingAs($user);

        return $user;
    }

    public function test_guest_is_redirected_from_profile_edit(): void
    {
        $this->get(route('admin.profile.edit'))->assertRedirect(route('login'));
    }

    public function test_edit_page_renders_and_is_prefilled(): void
    {
        $user = $this->actingAsAdmin();

        $profile = Profile::create([
            'user_id' => $user->id,
            'full_name' => 'Aldo Satrio',
            'tagline' => 'Full-Stack Engineer',
            'about_me' => 'Passionate developer.',
            'email' => 'aldo@example.com',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jakarta, Indonesia',
        ]);

        $this->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Aldo Satrio')
            ->assertSee('aldo@example.com')
            ->assertSee('+62 812 3456 7890');
    }

    public function test_update_saves_changes(): void
    {
        $user = $this->actingAsAdmin();

        $profile = Profile::create([
            'user_id' => $user->id,
            'full_name' => 'Old Name',
            'tagline' => 'Old Tagline',
            'about_me' => 'Old about.',
            'email' => 'old@example.com',
            'phone' => '+62 811 1111 1111',
            'address' => 'Old Address',
        ]);

        $this->put(route('admin.profile.update'), [
            'full_name' => 'Aldo Satrio Wicaksono',
            'tagline' => 'New Tagline',
            'about_me' => str_repeat('A', 50),
            'email' => 'aldo@example.com',
            'phone' => '+62 812 3456 7890',
            'address' => 'New Address, Jakarta',
        ])->assertRedirect(route('admin.profile.edit'));

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'full_name' => 'Aldo Satrio Wicaksono',
            'tagline' => 'New Tagline',
            'email' => 'aldo@example.com',
            'address' => 'New Address, Jakarta',
        ]);
    }

    public function test_update_validates_required_fields(): void
    {
        $this->actingAsAdmin();

        $this->put(route('admin.profile.update'), [])
            ->assertSessionHasErrors(['full_name', 'tagline', 'about_me', 'email', 'phone', 'address']);
    }

    public function test_update_rejects_invalid_email_and_phone(): void
    {
        $this->actingAsAdmin();

        $this->put(route('admin.profile.update'), [
            'full_name' => 'Aldo',
            'tagline' => 'Tagline',
            'about_me' => 'About',
            'email' => 'not-an-email',
            'phone' => 'abc!!!',
            'address' => 'Address',
        ])->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_update_processes_uploaded_image(): void
    {
        Storage::fake('public');

        $user = $this->actingAsAdmin();

        $profile = Profile::create([
            'user_id' => $user->id,
            'full_name' => 'Aldo',
            'tagline' => 'Tagline',
            'about_me' => 'About',
            'email' => 'aldo@example.com',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jakarta',
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg', 1000, 1000);

        $this->put(route('admin.profile.update'), [
            'full_name' => 'Aldo',
            'tagline' => 'Tagline',
            'about_me' => 'About',
            'email' => 'aldo@example.com',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jakarta',
            'profile_image' => $file,
        ])->assertRedirect(route('admin.profile.edit'));

        $profile->refresh();

        $this->assertNotNull($profile->profile_image);
        $this->assertStringStartsWith('profiles/', $profile->profile_image);
        Storage::disk('public')->assertExists($profile->profile_image);
    }

    public function test_update_deletes_old_image_when_replaced(): void
    {
        Storage::fake('public');

        $user = $this->actingAsAdmin();

        Storage::disk('public')->put('profiles/old.png', 'fake');

        $profile = Profile::create([
            'user_id' => $user->id,
            'full_name' => 'Aldo',
            'tagline' => 'Tagline',
            'about_me' => 'About',
            'email' => 'aldo@example.com',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jakarta',
            'profile_image' => 'profiles/old.png',
        ]);

        $this->put(route('admin.profile.update'), [
            'full_name' => 'Aldo',
            'tagline' => 'Tagline',
            'about_me' => 'About',
            'email' => 'aldo@example.com',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jakarta',
            'profile_image' => UploadedFile::fake()->image('new.jpg', 800, 800),
        ])->assertRedirect(route('admin.profile.edit'));

        Storage::disk('public')->assertMissing('profiles/old.png');

        $profile->refresh();
        $this->assertNotNull($profile->profile_image);
        Storage::disk('public')->assertExists($profile->profile_image);
    }

    public function test_update_removes_image_when_requested(): void
    {
        Storage::fake('public');

        $user = $this->actingAsAdmin();

        Storage::disk('public')->put('profiles/old.png', 'fake');

        $profile = Profile::create([
            'user_id' => $user->id,
            'full_name' => 'Aldo',
            'tagline' => 'Tagline',
            'about_me' => 'About',
            'email' => 'aldo@example.com',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jakarta',
            'profile_image' => 'profiles/old.png',
        ]);

        $this->put(route('admin.profile.update'), [
            'full_name' => 'Aldo',
            'tagline' => 'Tagline',
            'about_me' => 'About',
            'email' => 'aldo@example.com',
            'phone' => '+62 812 3456 7890',
            'address' => 'Jakarta',
            'remove_image' => '1',
        ])->assertRedirect(route('admin.profile.edit'));

        Storage::disk('public')->assertMissing('profiles/old.png');

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'profile_image' => null,
        ]);
    }
}
