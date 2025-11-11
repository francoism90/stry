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

    public function cacheRemember(string $key, int|float $ttl, mixed $value): mixed
    {
        $cacheKey = $this->generateCacheKey($key);

        return Cache::remember($cacheKey, now()->addSeconds($ttl), fn () => $value);
    }
}
