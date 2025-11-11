<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

use Illuminate\Support\Facades\Cache;

trait InteractsWithCache
{
    public function generateCacheKey(string $suffix): string
    {
        return "user:{$this->getKey()}:{$suffix}";
    }

    public function cacheRemember(string $key, int|float $ttl, mixed $value = null): mixed
    {
        $cacheKey = $this->generateCacheKey($key);

        return Cache::remember($cacheKey, now()->addSeconds($ttl), fn () => $value);
    }

    public function cacheValue(string $key, mixed $value, int|float $ttl): void
    {
        $cacheKey = $this->generateCacheKey($key);

        Cache::put($cacheKey, $value, now()->addSeconds($ttl));
    }

    public function cachedValue(string $key, mixed $default = null): mixed
    {
        $cacheKey = $this->generateCacheKey($key);

        return Cache::get($cacheKey, $default);
    }
}
