<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

trait CacheableModel
{
    public static function allCached(int $ttl = 3600): Collection
    {
        return Cache::remember(static::cacheKey(), $ttl, fn () => static::all());
    }

    public static function flushModelCache(): void
    {
        Cache::forget(static::cacheKey());
    }

    protected static function cacheKey(): string
    {
        return 'model_all_' . class_basename(static::class);
    }

    protected static function bootCacheableModel(): void
    {
        $flush = fn () => static::flushModelCache();

        static::created($flush);
        static::updated($flush);
        static::deleted($flush);
    }
}
