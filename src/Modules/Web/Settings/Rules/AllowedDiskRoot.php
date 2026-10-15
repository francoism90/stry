<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Rules;

use Closure;
use Domain\Shared\Enums\DiskRoot;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * A local disk must be a writable directory inside one of the disk roots, so a disk can't expose
 * other parts of the server, such as the application itself.
 */
class AllowedDiskRoot implements ValidationRule
{
    /**
     * @param  array<int, string>|null  $roots  the folders allowed, by default those of DiskRoot
     */
    public function __construct(
        protected ?array $roots = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $path = realpath((string) $value);

        if ($path === false || ! File::isDirectory($path) || ! File::isWritable($path)) {
            $fail(__('The path must be an existing, writable directory. Check that it is mounted in the container.'));

            return;
        }

        $roots = $this->getRoots();

        $isAllowed = collect($roots)
            ->map(fn (string $root): string => rtrim($root, '/'))
            ->contains(fn (string $root): bool => $path === $root || Str::startsWith($path, "{$root}/"));

        if (! $isAllowed) {
            $fail(__('The path must be inside :roots.', ['roots' => implode(', ', $roots)]));
        }
    }

    /**
     * @return array<int, string>
     */
    protected function getRoots(): array
    {
        return $this->roots ?? array_map(fn (DiskRoot $root): string => $root->value, DiskRoot::cases());
    }
}
