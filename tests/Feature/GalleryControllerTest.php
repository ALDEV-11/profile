<?php

namespace Tests\Feature;

use App\Enums\GalleryCategory;
use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create(['two_factor_enabled' => false]);
        $this->actingAs($user);

        return $user;
    }

    private function uploadableJpeg(): UploadedFile
    {
        return UploadedFile::fake()->image('photo.jpg', 800, 600);
    }

    public function test_guest_is_redirected_from_gallery_index(): void
    {
        $this->get('/222/galleries')->assertRedirect(route('login'));
    }

    public function test_index_lists_galleries(): void
    {
        $this->actingAsAdmin();

        Gallery::create([
            'user_id' => auth()->id(),
            'image_path' => 'galleries/abc-large.jpg',
            'caption' => 'Project Alpha',
            'category' => GalleryCategory::Project,
        ]);

        $this->get(route('admin.galleries.index'))
            ->assertOk()
            ->assertSee('Project Alpha');
    }

    public function test_index_searches_by_caption(): void
    {
        $this->actingAsAdmin();

        Gallery::create(['user_id' => auth()->id(), 'image_path' => 'galleries/a-large.jpg', 'caption' => 'Project Alpha', 'category' => GalleryCategory::Project]);
        Gallery::create(['user_id' => auth()->id(), 'image_path' => 'galleries/b-large.jpg', 'caption' => 'Conference', 'category' => GalleryCategory::Event]);

        $this->get(route('admin.galleries.index', ['q' => 'alpha']))
            ->assertOk()
            ->assertSee('Project Alpha')
            ->assertDontSee('Conference');
    }

    public function test_create_page_renders(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.galleries.create'))->assertOk();
    }

    public function test_store_validates_and_saves(): void
    {
        Storage::fake('public');
        $user = $this->actingAsAdmin();

        $this->post(route('admin.galleries.store'), [
            'image' => $this->uploadableJpeg(),
            'caption' => 'Project Alpha',
            'category' => 'project',
        ])->assertRedirect(route('admin.galleries.index'));

        $gallery = Gallery::first();

        $this->assertNotNull($gallery);
        $this->assertDatabaseHas('galleries', [
            'user_id' => $user->id,
            'caption' => 'Project Alpha',
            'category' => 'project',
        ]);

        Storage::disk('public')->assertExists($gallery->image_path);
        Storage::disk('public')->assertExists(str_replace('-large.jpg', '-thumb.jpg', $gallery->image_path));
        Storage::disk('public')->assertExists(str_replace('-large.jpg', '-medium.jpg', $gallery->image_path));
    }

    public function test_store_rejects_missing_image_and_category(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.galleries.store'), [
            'caption' => 'No image',
        ])->assertSessionHasErrors(['image', 'category']);

        $this->assertDatabaseCount('galleries', 0);
    }

    public function test_store_rejects_invalid_file_format(): void
    {
        $this->actingAsAdmin();

        $this->post(route('admin.galleries.store'), [
            'image' => UploadedFile::fake()->create('document.pdf', 100),
            'category' => 'project',
        ])->assertSessionHasErrors('image');
    }

    public function test_edit_page_is_prefilled(): void
    {
        $this->actingAsAdmin();

        $gallery = Gallery::create([
            'user_id' => auth()->id(),
            'image_path' => 'galleries/abc-large.jpg',
            'caption' => 'Project Alpha',
            'category' => GalleryCategory::Project,
        ]);

        $this->get(route('admin.galleries.edit', $gallery))
            ->assertOk()
            ->assertSee('Project Alpha');
    }

    public function test_update_updates_caption_and_category(): void
    {
        $this->actingAsAdmin();

        $gallery = Gallery::create([
            'user_id' => auth()->id(),
            'image_path' => 'galleries/abc-large.jpg',
            'caption' => 'Project Alpha',
            'category' => GalleryCategory::Project,
        ]);

        $this->put(route('admin.galleries.update', $gallery), [
            'caption' => 'Updated caption',
            'category' => 'event',
        ])->assertRedirect(route('admin.galleries.index'));

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'caption' => 'Updated caption',
            'category' => 'event',
        ]);
    }

    public function test_update_replaces_image_when_provided(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();

        $gallery = Gallery::create([
            'user_id' => auth()->id(),
            'image_path' => 'galleries/abc-large.jpg',
            'caption' => 'Project Alpha',
            'category' => GalleryCategory::Project,
        ]);

        Storage::disk('public')->put('galleries/abc-large.jpg', 'old');
        Storage::disk('public')->put('galleries/abc-thumb.jpg', 'old');
        Storage::disk('public')->put('galleries/abc-medium.jpg', 'old');

        $this->put(route('admin.galleries.update', $gallery), [
            'image' => $this->uploadableJpeg(),
            'caption' => 'New photo',
            'category' => 'project',
        ])->assertRedirect(route('admin.galleries.index'));

        $gallery->refresh();
        $this->assertNotSame('galleries/abc-large.jpg', $gallery->image_path);
        Storage::disk('public')->assertMissing('galleries/abc-large.jpg');
        Storage::disk('public')->assertExists($gallery->image_path);
    }

    public function test_destroy_soft_deletes_and_removes_files(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();

        $gallery = Gallery::create([
            'user_id' => auth()->id(),
            'image_path' => 'galleries/abc-large.jpg',
            'caption' => 'Project Alpha',
            'category' => GalleryCategory::Project,
        ]);

        Storage::disk('public')->put('galleries/abc-large.jpg', 'x');
        Storage::disk('public')->put('galleries/abc-thumb.jpg', 'x');
        Storage::disk('public')->put('galleries/abc-medium.jpg', 'x');

        $this->delete(route('admin.galleries.destroy', $gallery))
            ->assertRedirect(route('admin.galleries.index'));

        $this->assertSoftDeleted('galleries', ['id' => $gallery->id]);
        Storage::disk('public')->assertMissing('galleries/abc-large.jpg');
        Storage::disk('public')->assertMissing('galleries/abc-thumb.jpg');
        Storage::disk('public')->assertMissing('galleries/abc-medium.jpg');
    }
}
