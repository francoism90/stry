<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Domain\Shared\Contracts\Enumerable;
use Illuminate\Support\Facades\File;

/**
 * The folders local disks can be created in. Each must be mounted in the containers before a disk
 * can use it. The application's own folders (/app, /data, /config and /cache) are never listed.
 */
enum DiskRoot: string implements Enumerable
{
    case Media = '/media';
    case Import = '/import';
    case Exports = '/exports';
    case Mnt = '/mnt';
    case Storage = '/storage';
    case Downloads = '/downloads';
    case Vault = '/vault';
    case Videos = '/videos';
    case Movies = '/movies';
    case Tv = '/tv';
    case Shows = '/shows';
    case Library = '/library';
    case Nas = '/nas';
    case Volumes = '/volumes';
    case SrvMedia = '/srv/media';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $root): array => [$root->value => $root->value])
            ->all();
    }

    /**
     * Whether the folder exists in the container, which means it has been mounted.
     */
    public function isMounted(): bool
    {
        return File::isDirectory($this->value);
    }
}
