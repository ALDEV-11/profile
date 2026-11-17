<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Clears the cached public homepage and admin dashboard payloads whenever
 * content changes. Without this the caches would stay stale until their TTL
 * expires.
 */
trait ClearsContentCaches
{
    public static function bootClearsContentCaches(): void
    {
        static::saved(static fn () => static::clearContentCaches());
        static::deleted(static fn () => static::clearContentCaches());
    }

    protected static function clearContentCaches(): void
    {
        foreach ([
            'home.page.data',
            'admin.dashboard.overview',
            'admin.messages.unread',
            'admin.messages.recent',
        ] as $key) {
            Cache::forget($key);
        }
    }
}
