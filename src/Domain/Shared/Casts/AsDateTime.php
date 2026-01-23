<?php

declare(strict_types=1);

namespace Domain\Shared\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class AsDateTime implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Carbon::parse($value)
            ->timezone(Config::string('app.timezone', 'UTC'))
            ->toDateTimeString();
    }
}
