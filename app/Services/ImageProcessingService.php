<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\EncodedImage;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;

class ImageProcessingService
{
    /**
     * Sizes (max edge, aspect preserved) generated for each gallery photo.
     *
     * @var array<string, int>
     */
    public const SIZES = [
        'thumb' => 300,
        'medium' => 600,
        'large' => 1200,
    ];

    /**
     * Output quality for the JPEG encoder.
     */
    public const JPEG_QUALITY = 80;

    /**
     * Resize an image to fit within the given box, preserving aspect ratio.
     */
    public function resize(ImageInterface $image, int $maxEdge): ImageInterface
    {
        return $image->scaleDown($maxEdge, $maxEdge);
    }

    /**
     * Encode the image as an optimised JPEG (quality 80).
     */
    public function optimize(ImageInterface $image): EncodedImage
    {
        return $image->encodeUsingMediaType('image/jpeg', self::JPEG_QUALITY);
    }

    /**
     * Decode an uploaded file into an Intervention image.
     */
    public function decode(UploadedFile $file): ImageInterface
    {
        return InterventionImage::decode($file->getRealPath());
    }

    /**
     * Generate the three gallery sizes (thumb / medium / large) for an upload.
     *
     * Follows the existing "{slug}-{size}.jpg" convention so the public
     * gallery imageUrl()/srcset() helpers keep resolving every variant.
     * The large path is returned (and persisted in the galleries.image_path
     * column); the other two variants are derived from it by the model.
     *
     * @return string the stored "large" path, e.g. "galleries/abcd-large.jpg"
     */
    public function generateGallerySizes(UploadedFile $file, string $directory = 'galleries'): string
    {
        $slug = Str::uuid();

        foreach (self::SIZES as $name => $maxEdge) {
            $image = $this->decode($file);
            $variant = $this->resize($image, $maxEdge);
            $encoded = $this->optimize($variant);

            Storage::disk('public')->put(
                trim($directory, '/')."/{$slug}-{$name}.jpg",
                (string) $encoded,
                [
                    'ContentType' => 'image/jpeg',
                    'CacheControl' => 'public, max-age=31536000, immutable',
                ],
            );
        }

        return trim($directory, '/')."/{$slug}-large.jpg";
    }

    /**
     * Delete all generated variants for a gallery record.
     *
     * @param  string|null  $largePath  the stored "large" path, e.g. "galleries/abcd-large.jpg"
     */
    public function deleteGallery(?string $largePath): void
    {
        if (empty($largePath) || ! Str::contains($largePath, 'galleries/')) {
            return;
        }

        $disk = Storage::disk('public');
        $base = Str::replaceLast('-large.jpg', '', $largePath);

        foreach (array_keys(self::SIZES) as $name) {
            $path = "{$base}-{$name}.jpg";

            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}
