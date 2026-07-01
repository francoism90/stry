<?php

declare(strict_types=1);

namespace Domain\Shared\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * @mixin Model
 */
trait InteractsWithCache
{
    public static function bootInteractsWithCache(): void
    {
        self::created(fn () => static::clearResponseCache());
        self::updated(fn () => static::clearResponseCache());
        self::deleted(fn () => static::clearResponseCache());
    }

    public static function clearResponseCache(): void
    {
        ResponseCache::clear(static::responseCacheTags());
    }

    public static function responseCacheTags(): array
    {
        $name = Str::snake(class_basename(static::class));

        return [$name, Str::plural($name)];
    }
}
