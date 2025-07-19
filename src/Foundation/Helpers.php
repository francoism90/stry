<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;

if (! function_exists('inertia_route')) {
    function inertia_route(string $name, mixed $parameters = []): string
    {
        return route($name, $parameters, absolute: false);
    }
}

if (! function_exists('duration')) {
    function duration(mixed $value): string
    {
        $time = Carbon::parse($value)
            ->utc()
            ->toTimeString();

        return preg_replace('/^0(?:0:0?)?/', '', $time);
    }
}
