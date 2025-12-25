<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

use Closure;
use Illuminate\Support\Facades\Cache;

trait InteractsWithCache
{
    public function generateCacheKey(string $suffix): string
    {
        return "user:{$this->getKey()}:{$suffix}";
    }

    public function cacheRemember(string $key, mixed $ttl, ?Closure $value = null): mixed
    {
        $cacheKey = $this->generateCacheKey($key);

        return Cache::remember($cacheKey, $ttl, $value);
    }

    public function cacheValue(string $key, mixed $value, mixed $ttl = null): void
    {
        $cacheKey = $this->generateCacheKey($key);

        Cache::put($cacheKey, $value, $ttl);
    }

    public function cachedValue(string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->generateCacheKey($key);

        return Cache::get($cacheKey, $default);
    }
}
