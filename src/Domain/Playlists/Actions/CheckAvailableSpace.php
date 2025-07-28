<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Exceptions\DiskUsageException;
use Domain\Playlists\Models\Playlist;
use Illuminate\Filesystem\LocalFilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class CheckAvailableSpace
{
    public function handle(): void
    {
        if (Playlist::shouldUseRotationKeys()) {
            $this->checkFreeSpace(
                disk: Playlist::getRotationKeyDisk(),
                limit: config('playlist.rotation_keys_disk_size', 0),
            );
        }

        $this->checkFreeSpace(
            disk: Playlist::getTranscodeDisk(),
            limit: config('playlist.disk_size', 0),
        );
    }

    protected function checkFreeSpace(string $disk, int $limit = 0): void
    {
        $adapter = Storage::disk($disk);

        if ($adapter instanceof LocalFilesystemAdapter) {
            $this->checkLocalFreeSpace($adapter, $limit);
        }
    }

    protected function checkLocalFreeSpace(LocalFilesystemAdapter $adapter, int $limit = 0): void
    {
        $absolutePath = $adapter->path('');

        $freeSpace = round(disk_free_space($absolutePath)) ?: 0;

        throw_if($limit > 0 && ($limit < $freeSpace), DiskUsageException::exceededLimit($absolutePath, $freeSpace));
    }
}
