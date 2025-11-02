<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

trait InteractsWithCache
{
    public function generateCacheKey(string $suffix): string
    {
        return hash('xxh128', "user:{$this->getKey()}:{$suffix}");
    }
}
