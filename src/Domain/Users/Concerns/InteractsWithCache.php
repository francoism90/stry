<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

use Domain\Users\Models\User;
use Spatie\ResponseCache\Facades\ResponseCache;

trait InteractsWithCache
{
    public static function bootInteractsWithCache(): void
    {
        static::updated(fn (User $model) => static::clearResponseCache($model));
        static::deleted(fn (User $model) => static::clearResponseCache($model));
    }

    public static function clearResponseCache(User $model): void
    {
        ResponseCache::clear(['user-'.$model->getKey()]);
    }
}
