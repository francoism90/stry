<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Exceptions\DiskUsageException;
use Domain\Playlists\Models\Playlist;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\LocalFilesystemAdapter;

class CheckAvailableSpace
{
    public function handle(Playlist $playlist): void
    {
        if (Playlist::shouldUseRotationKeys() && filled($playlist->secret_disk)) {
            $this->checkFreeSpace(
                adapter: $playlist->getSecretFilesystem(),
                limit: config('playlist.rotation_keys_disk_size', 0),
            );
        }

        $this->checkFreeSpace(
            adapter: $playlist->getFilesystem(),
            limit: config('playlist.disk_size', 0),
        );
    }

    protected function checkFreeSpace(FilesystemAdapter $adapter, int $limit = 0): void
    {
        if ($adapter instanceof LocalFilesystemAdapter) {
            $this->checkLocalFreeSpace($adapter, $limit);
        }
    }

    protected function checkLocalFreeSpace(LocalFilesystemAdapter $adapter, int $limit = 0): void
    {
        $absolutePath = $adapter->path('');

        $freeSpace = round(disk_free_space($absolutePath)) ?: 0;

        throw_if($limit > 0 && ($limit > $freeSpace), DiskUsageException::exceededLimit($absolutePath, $freeSpace));
    }
}
