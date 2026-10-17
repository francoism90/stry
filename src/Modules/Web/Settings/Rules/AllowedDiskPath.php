<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Rules;

use Closure;
use Domain\Shared\Enums\DiskDriver;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * The path of a local disk must be a writable directory that isn't inside, or around, a forbidden folder,
 * so a disk can't expose other parts of the server, such as the application itself. The paths of other
 * disks, such as an S3 prefix, are left alone.
 */
class AllowedDiskPath implements DataAwareRule, ValidationRule
{
    /**
     * The application's own folders and those of the system.
     *
     * @var array<int, string>
     */
    public const array FORBIDDEN = [
        '/app',
        '/data',
        '/config',
        '/cache',
        '/tmp',
        '/bin',
        '/boot',
        '/dev',
        '/etc',
        '/lib',
        '/lib64',
        '/proc',
        '/root',
        '/run',
        '/sbin',
        '/sys',
        '/usr',
        '/var',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  array<int, string>|null  $forbidden  the folders not allowed, by default FORBIDDEN and the application's folder
     */
    public function __construct(
        protected ?array $forbidden = null,
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

        $isForbidden = collect($this->getForbidden())
            ->contains(fn (string $folder): bool => $this->isWithin($path, $folder) || $this->isWithin($folder, $path));

        if ($isForbidden) {
            $fail(__('The path can\'t be inside or contain :folders.', ['folders' => implode(', ', $this->getForbidden())]));
        }
    }

    protected function isWithin(string $path, string $folder): bool
    {
        $folder = rtrim($folder, '/');

        return $path === $folder || Str::startsWith($path, "{$folder}/");
    }

    /**
     * @return array<int, string>
     */
    protected function getForbidden(): array
    {
        return collect($this->forbidden ?? [...self::FORBIDDEN, base_path()])
            ->map(fn (string $folder): string => realpath($folder) ?: $folder)
            ->unique()
            ->values()
            ->all();
    }
}
