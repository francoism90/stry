<?php

declare(strict_types=1);

namespace Domain\Shared\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasUlidRouteKey
{
    use HasUlids;

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public static function findFromUlid(self|string $value): ?static
    {
        if ($value instanceof static) {
            return $value;
        }

        return static::query()->firstWhere('ulid', $value);
    }
}
