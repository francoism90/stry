<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Domain\Videos\Models\Video;
use Spatie\ResponseCache\Facades\ResponseCache;

trait InteractsWithCache
{
    public static function bootInteractsWithCache(): void
    {
        static::updated(fn (Video $model) => static::clearResponseCache($model));

        static::deleted(fn (Video $model) => static::clearResponseCache($model));
    }

    public static function clearResponseCache(Video $model): void
    {
        ResponseCache::clear(['video-'.$model->getKey()]);
    }
}
