<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Rules;

use Closure;
use Domain\Shared\Enums\DiskDriver;
use Domain\Shared\Enums\DiskRoot;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * The path of a local disk must be a writable directory inside one of the disk roots, so a disk can't
 * expose other parts of the server, such as the application itself. The paths of other disks, such as
 * an S3 prefix, are left alone.
 */
class AllowedDiskRoot implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  array<int, string>|null  $roots  the folders allowed, by default those of DiskRoot
     */
    public function __construct(
        protected ?array $roots = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $driver = data_get($this->data, Str::beforeLast($attribute, '.').'.driver');

        if ($driver !== DiskDriver::Local->value) {
            return;
        }

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
