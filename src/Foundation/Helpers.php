<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Inertia\Inertia;

if (! function_exists('duration')) {
    /**
     * Format a length in seconds as m:ss, or h:mm:ss from an hour on, without wrapping after 24 hours.
     */
    function duration(float|int $seconds): string
    {
        $seconds = (int) $seconds;

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainder = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $remainder);
        }

        return sprintf('%d:%02d', $minutes, $remainder);
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
