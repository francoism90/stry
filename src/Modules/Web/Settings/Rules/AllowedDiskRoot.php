<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * A local disk must be a writable directory inside one of the allowed roots, so a disk can't expose
 * other parts of the server, such as the application itself.
 */
class AllowedDiskRoot implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $path = realpath((string) $value);

        if ($path === false || ! File::isDirectory($path) || ! File::isWritable($path)) {
            $fail(__('The :attribute must be an existing, writable directory.'));

            return;
        }

        $isAllowed = collect(Config::array('filesystems.allowed_roots', []))
            ->map(fn (string $root): string => rtrim($root, '/'))
            ->contains(fn (string $root): bool => $path === $root || Str::startsWith($path, "{$root}/"));

        if (! $isAllowed) {
            $fail(__('The :attribute must be inside :roots.', [
                'roots' => implode(', ', Config::array('filesystems.allowed_roots', [])),
            ]));
        }
    }
}
