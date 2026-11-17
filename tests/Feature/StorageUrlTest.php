<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageUrlTest extends TestCase
{
    public function test_s3_public_disk_does_not_prefix_urls_with_the_local_storage_path(): void
    {
        Storage::forgetDisk('public');

        config([
            'filesystems.disks.public.driver' => 's3',
            'filesystems.disks.public.root' => '',
            'filesystems.disks.public.url' => 'https://cdn.example.com/profile-assets',
            'filesystems.disks.public.key' => 'test-key',
            'filesystems.disks.public.secret' => 'test-secret',
            'filesystems.disks.public.region' => 'us-east-1',
            'filesystems.disks.public.bucket' => 'profile-assets',
            'filesystems.disks.public.endpoint' => 'https://example.com',
        ]);

        $this->assertSame(
            'https://cdn.example.com/profile-assets/profiles/avatar.jpg',
            Storage::disk('public')->url('profiles/avatar.jpg'),
        );
    }
}
