<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

trait InteractsWithCache
{
    public function getCacheKey(string $suffix): string
    {
        return hash('xxh128', "user:{$this->getKey()}:{$suffix}");
    }

    public function hasCache(string $suffix): bool
    {
        $cacheKey = $this->getCacheKey($suffix);

        return Cache::has($cacheKey);
    }

    public function forgetCache(string $suffix): void
    {
        $cacheKey = $this->getCacheKey($suffix);

        Cache::forget($cacheKey);
    }

    public function putCache(string $suffix, mixed $value, DateTimeInterface|DateInterval|int $ttl): void
    {
        $cacheKey = $this->getCacheKey($suffix);

        Cache::put($cacheKey, $value, $ttl);
    }

    public function addCache(string $suffix, mixed $value, DateTimeInterface|DateInterval|int $ttl): bool
    {
        $cacheKey = $this->getCacheKey($suffix);

        return Cache::add($cacheKey, $value, $ttl);
    }

    public function getCacheValue(string $suffix, mixed $default = null): mixed
    {
        $cacheKey = $this->getCacheKey($suffix);

        return Cache::get($cacheKey, $default);
    }
}
