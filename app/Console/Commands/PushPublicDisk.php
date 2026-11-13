<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PushPublicDisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:push-public
                            {--all : Upload every local file, including unreferenced leftovers}
                            {--dry-run : List the files that would be uploaded}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload the images referenced by the database to the configured public disk (R2/S3)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = storage_path('app/public');

        if (! File::isDirectory($source)) {
            $this->error("Source directory not found: {$source}");

            return self::FAILURE;
        }

        $files = collect(File::allFiles($source))
            ->reject(fn ($file) => $file->getFilename() === '.gitignore')
            ->values();

        if (! $this->option('all')) {
            $referenced = $this->referencedPaths();

            $missing = array_values(array_diff($referenced, $files->map(
                fn ($file) => str_replace('\\', '/', $file->getRelativePathname())
            )->all()));

            if ($missing !== []) {
                $this->error('These database records point at files that do not exist locally:');

                foreach ($missing as $path) {
                    $this->line("  {$path}");
                }

                return self::FAILURE;
            }

            $files = $files
                ->filter(fn ($file) => in_array(
                    str_replace('\\', '/', $file->getRelativePathname()),
                    $referenced,
                    true,
                ))
                ->values();
        }

        if ($files->isEmpty()) {
            $this->info('No files to upload.');

            return self::SUCCESS;
        }

        $driver = config('filesystems.disks.public.driver');

        if ($driver === 'local' && ! $this->option('dry-run')) {
            $this->error('The public disk is still local. Set PUBLIC_DISK_DRIVER=s3 and the AWS_* variables first.');

            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $uploaded = 0;
        $bytes = 0;

        foreach ($files as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $mime = File::mimeType($file->getPathname()) ?: 'application/octet-stream';

            if ($this->option('dry-run')) {
                $this->line(sprintf('  %-64s %s', $relative, $mime));

                continue;
            }

            $stored = $disk->put($relative, File::get($file->getPathname()), [
                'ContentType' => $mime,
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]);

            if (! $stored) {
                $this->error("Failed to upload {$relative}");

                return self::FAILURE;
            }

            $uploaded++;
            $bytes += $file->getSize();
            $this->line("  uploaded {$relative}");
        }

        if ($this->option('dry-run')) {
            $this->info($files->count().' file(s) would be uploaded.');
        } else {
            $this->info("Uploaded {$uploaded} file(s), ".number_format($bytes / 1024, 1).' KB.');
        }

        return self::SUCCESS;
    }

    /**
     * Image paths referenced by the current database connection.
     *
     * @return array<int, string>
     */
    protected function referencedPaths(): array
    {
        $paths = [];

        foreach (Gallery::query()->pluck('image_path') as $imagePath) {
            if (! Str::contains($imagePath, 'galleries/')) {
                continue;
            }

            $base = Str::replaceLast('-large.jpg', '', $imagePath);

            foreach (['thumb', 'medium', 'large'] as $size) {
                $paths[] = "{$base}-{$size}.jpg";
            }
        }

        foreach (Profile::query()->pluck('profile_image') as $imagePath) {
            if (filled($imagePath) && Str::startsWith($imagePath, ['profiles/', 'galleries/'])) {
                $paths[] = $imagePath;
            }
        }

        return array_values(array_unique($paths));
    }
}
