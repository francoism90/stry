<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;

class CleanupPlaylistFilesystem
{
    public function handle(Playlist $playlist): void
    {
        // Delete the playlist directory
        if ($playlist->getFilesystem()->directoryExists($playlist->getPath())) {
            $playlist->getFilesystem()->deleteDirectory($playlist->getPath());
        }
    }
}
