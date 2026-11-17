<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Foundation\Settings\StorageSettings;
use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum DiskContent: string implements Enumerable
{
    use HasLabels;

    case Videos = 'videos';
    case Captions = 'captions';
    case Conversions = 'conversions';
    case Thumbnails = 'thumbnails';
    case Reels = 'reels';
    case Transcodes = 'transcodes';
    case Import = 'import';
    case Exports = 'exports';
    case Snapshots = 'snapshots';

    public function label(): string
    {
        return match ($this) {
            self::Videos => __('Videos'),
            self::Captions => __('Captions'),
            self::Conversions => __('Conversions'),
            self::Thumbnails => __('Thumbnails'),
            self::Reels => __('Reels'),
            self::Transcodes => __('Transcodes'),
            self::Import => __('Import'),
            self::Exports => __('Exports'),
            self::Snapshots => __('Snapshots'),
        };
    }

    /**
     * The name of the disk new files for this content are written to.
     */
    public function disk(): string
    {
        return rescue(
            fn (): ?string => app(StorageSettings::class)->defaultDiskFor($this)?->name,
            report: false,
        ) ?? $this->defaultDisk();
    }

    public function defaultDisk(): string
    {
        return match ($this) {
            self::Videos, self::Captions => 'media',
            self::Conversions, self::Thumbnails, self::Reels => 'conversions',
            self::Transcodes => 'cache',
            self::Import => 'import',
            self::Exports => 'exports',
            self::Snapshots => 'snapshots',
        };
    }

    /**
     * Whether the files are shown in the browser through temporary URLs.
     */
    public function requiresTemporaryUrls(): bool
    {
        return in_array($this, [self::Conversions, self::Thumbnails, self::Reels], true);
    }

    /**
     * Whether ffmpeg writes scratch output here, which is much faster on a local disk.
     */
    public function prefersLocal(): bool
    {
        return $this === self::Transcodes;
    }
}
