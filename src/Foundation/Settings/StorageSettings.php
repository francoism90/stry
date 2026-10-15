<?php

declare(strict_types=1);

namespace Foundation\Settings;

use Domain\Shared\DataObjects\StorageCredentials;
use Domain\Shared\DataObjects\StorageDisk;
use Domain\Shared\Enums\DiskContent;
use Spatie\LaravelSettings\Settings;

class StorageSettings extends Settings
{
    /** @var array<int, StorageDisk> */
    public array $disks = [];

    /**
     * The access keys of S3 disks, by disk name. Stored encrypted.
     *
     * @var array<string, StorageCredentials>
     */
    public array $credentials = [];

    public static function group(): string
    {
        return 'storage';
    }

    /** @return array<int, string> */
    public static function encrypted(): array
    {
        return ['credentials'];
    }

    public function find(string $name): ?StorageDisk
    {
        return collect($this->disks)->first(fn (StorageDisk $disk): bool => $disk->name === $name);
    }

    public function defaultDiskFor(DiskContent $content): ?StorageDisk
    {
        return collect($this->disks)->first(fn (StorageDisk $disk): bool => $disk->isDefaultFor($content));
    }

    /** @return array<int, StorageDisk> */
    public function disksFor(DiskContent $content): array
    {
        return collect($this->disks)
            ->filter(fn (StorageDisk $disk): bool => $disk->contains($content))
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function toFilesystemConfig(): array
    {
        return collect($this->disks)
            ->mapWithKeys(fn (StorageDisk $disk): array => [$disk->name => $disk->toConfig($this->credentials[$disk->name] ?? null)])
            ->all();
    }
}
