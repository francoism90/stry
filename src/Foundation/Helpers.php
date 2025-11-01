<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

if (! function_exists('duration')) {
    function duration(mixed $value): string
    {
        $time = Carbon::parse($value)
            ->utc()
            ->toTimeString();

        return preg_replace('/^0(?:0:0?)?/', '', $time);
    }
}

if (! function_exists('markdown')) {
    function markdown(string $value = ''): string
    {
        return Str::markdown($value, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
