<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use Illuminate\Support\Facades\DB;

class UpdatePlaylistActivity
{
    public function handle(Playlist $playlist): Playlist
    {
        return DB::transaction(function () use ($playlist) {
            // Mark the playlist as accessed
            $playlist->touchQuietly('accessed_at');

            return $playlist;
        });
    }
}
