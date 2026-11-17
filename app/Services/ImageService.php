<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;
use RuntimeException;

class ImageService
{
    /**
     * Target dimension for square profile images.
     */
    public const PROFILE_SIZE = 500;

    /**
     * Resize an uploaded image to a square and store it on the public disk.
     *
     * @param  string  $directory  e.g. "profiles"
     * @return string the stored path relative to the public disk, e.g. "profiles/abc.jpg"
     */
    public function storeSquare(UploadedFile $file, string $directory, int $size = self::PROFILE_SIZE): string
    {
        $image = $this->decode($file);

        // Center-crop to a perfect square, then downscale.
        $image->cover($size, $size);

        $extension = $this->pickExtension($image, $file);
        $path = $this->destinationPath($directory, $extension);

        $options = $extension === 'png' ? [] : ['quality' => 80];
        $encoded = $image->encodeUsingFileExtension($extension, ...$options);

        Storage::disk('public')->put($path, (string) $encoded, [
            'ContentType' => $encoded->mediaType(),
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        return $path;
    }

    /**
     * Delete an image from the public disk when it lives inside storage
     * (e.g. "profiles/…"). Legacy records stored in public/ are left alone.
     */
    public function deleteStored(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        if (! Str::startsWith($path, ['profiles/', 'galleries/'])) {
            return;
        }

        // R2/S3 delete is idempotent, so skip the extra HEAD request.
        Storage::disk('public')->delete($path);
    }

    /**
     * Decode an uploaded file into an Intervention image.
     */
    protected function decode(UploadedFile $file): ImageInterface
    {
        return InterventionImage::decode($file->getRealPath());
    }

    /**
     * Prefer the original extension when it is a safe image format,
     * otherwise fall back to the image's own mime-derived extension.
     */
    protected function pickExtension(ImageInterface $image, UploadedFile $file): string
    {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array(strtolower($file->getClientOriginalExtension()), $allowed, true)) {
            return strtolower($file->getClientOriginalExtension()) === 'jpeg' ? 'jpg' : strtolower($file->getClientOriginalExtension());
        }

        try {
            $mime = $image->mime();
            $map = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];

            return $map[$mime] ?? 'jpg';
        } catch (RuntimeException) {
            return 'jpg';
        }
    }

    /**
     * Build a unique destination path inside the public disk.
     */
    protected function destinationPath(string $directory, string $extension): string
    {
        return trim($directory, '/').'/'.Str::uuid().'.'.$extension;
    }
}
