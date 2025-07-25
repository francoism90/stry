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
        if (Playlist::shouldUseRotationKeys()) {
            $this->checkDiskUsage(
                disk: Playlist::getRotationKeyDisk(),
                maxUsage: config('playlist.rotation_keys_disk_max_usage', 1073741824 * 1), // 1GB fallback
            );
        }

        $this->checkDiskUsage(
            disk: Playlist::getTranscodeDisk(),
            maxUsage: config('playlist.disk_max_usage', 1073741824 * 5), // 5GB fallback
        );
    }

    protected function checkDiskUsage(string $disk, float|int $maxUsage): void
    {
        $adapter = Storage::disk($disk);

        if ($adapter instanceof LocalFilesystemAdapter) {
            $this->checkLocalDiskUsage($adapter, $maxUsage);
        }
    }

    protected function checkLocalDiskUsage(LocalFilesystemAdapter $adapter, float|int $maxUsage): void
    {
        $absolutePath = $adapter->path('');

        $totalSpace = disk_total_space($absolutePath);
        $freeSpace = disk_free_space($absolutePath);

        $usedSpace = $totalSpace - $freeSpace;
        $usageSize = $usedSpace / 1073741824;

        throw_if($usageSize > floatval($maxUsage), DiskUsageException::exceededUsage($absolutePath, $maxUsage));
    }
}
