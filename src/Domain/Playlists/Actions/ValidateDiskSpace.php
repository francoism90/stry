<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Exceptions\DiskUsageException;
use Domain\Playlists\Models\Playlist;
use Illuminate\Filesystem\LocalFilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class ValidateDiskSpace
{
    public function handle(): void
    {
        $disks = collect([
            Playlist::getRotationKeyDisk(),
            Playlist::getTranscodeDisk(),
        ]);

        $maxDiskUsage = floatval(Playlist::getMaxDiskUsage());

        $disks->each(function (string $disk) use ($maxDiskUsage) {
            $adapter = Storage::disk($disk);

            if ($adapter instanceof LocalFilesystemAdapter) {
                $this->checkLocalDiskSpace($adapter, $maxDiskUsage);
            }
        });
    }

    protected function checkLocalDiskSpace(LocalFilesystemAdapter $adapter, float $maxDiskUsage): void
    {
        $absolutePath = $adapter->path('');

        $totalSpace = disk_total_space($absolutePath);
        $freeSpace = disk_free_space($absolutePath);

        $diskUsage = $totalSpace - $freeSpace;
        $usageSize = $diskUsage / 1073741824;

        throw_if($usageSize > $maxDiskUsage, DiskUsageException::exceededUsage($absolutePath, $maxDiskUsage));
    }
}
