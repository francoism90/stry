<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;

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
    function markdown(?string $value = null): string
    {
        if (blank($value)) {
            return '';
        }

        return Str::markdown($value, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}

if (! function_exists('toast')) {
    /**
     * Flash a toast notification to the next Inertia response.
     */
    function toast(string $title, string $description, string $type = 'success'): void
    {
        Inertia::flash([
            'title' => $title,
            'description' => $description,
            'type' => $type,
        ]);
    }
}
