<?php

namespace App\Services;

use App\Models\Gallery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;

class GalleryService
{
    public function __construct(
        protected ImageProcessingService $images,
    ) {}

    /**
     * List the user's gallery photos, newest first (12 per page).
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(int $userId, array $filters = []): LengthAwarePaginator
    {
        return Gallery::query()
            ->where('user_id', $userId)
            ->when($filters['q'] ?? null, function (Builder $query, string $q) {
                $query->where(function (Builder $inner) use ($q) {
                    $inner->whereLike('caption', "%{$q}%", caseSensitive: false)
                        ->orWhereLike('category', "%{$q}%", caseSensitive: false);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * Persist a new gallery photo (with all generated image sizes).
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $userId, array $data, UploadedFile $image): Gallery
    {
        $data['user_id'] = $userId;
        $data['image_path'] = $this->images->generateGallerySizes($image, 'galleries');
        $data['order'] = $data['order'] ?? $this->nextOrder($userId);

        return Gallery::create($data);
    }

    /**
     * Update a gallery record, optionally replacing the photo.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Gallery $gallery, array $data, ?UploadedFile $image = null): Gallery
    {
        if ($image) {
            $this->images->deleteGallery($gallery->image_path);
            $data['image_path'] = $this->images->generateGallerySizes($image, 'galleries');
        }

        $gallery->update($data);

        return $gallery;
    }

    /**
     * Soft delete a gallery record and remove its image files.
     */
    public function delete(Gallery $gallery): void
    {
        $this->images->deleteGallery($gallery->image_path);
        $gallery->delete();
    }

    /**
     * Next display order value for the user.
     */
    public function nextOrder(int $userId): int
    {
        return (int) Gallery::query()
            ->where('user_id', $userId)
            ->max('order') + 1;
    }
}
