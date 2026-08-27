<?php

namespace App\Models;

use App\Enums\GalleryCategory;
use Database\Factories\GalleryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'image_path', 'caption', 'category', 'order'])]
class Gallery extends Model
{
    /** @use HasFactory<GalleryFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => GalleryCategory::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderByDesc('created_at');
    }

    /**
     * Absolute URL for a given image size.
     *
     * Gallery images follow the {slug}-large.jpg / -medium.jpg / -thumb.jpg
     * convention inside storage/app/public/galleries. Images outside the
     * galleries directory (legacy records) fall back to their stored path.
     */
    public function imageUrl(string $size = 'large'): string
    {
        if (! Str::contains($this->image_path, 'galleries/')) {
            return asset($this->image_path);
        }

        $suffix = match ($size) {
            'thumb' => '-thumb.jpg',
            'medium' => '-medium.jpg',
            default => '-large.jpg',
        };

        return asset('storage/'.Str::replaceLast('-large.jpg', $suffix, $this->image_path));
    }

    /**
     * Responsive srcset string with thumbnail, medium and large variants.
     */
    public function srcset(): string
    {
        if (! Str::contains($this->image_path, 'galleries/')) {
            return '';
        }

        return sprintf(
            '%s 300w, %s 600w, %s 1200w',
            $this->imageUrl('thumb'),
            $this->imageUrl('medium'),
            $this->imageUrl('large')
        );
    }
}
