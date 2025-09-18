<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use Illuminate\Support\Facades\DB;

class UpdatePlaylistDetails
{
    public function handle(Playlist $playlist, array $attributes = []): Playlist
    {
        return DB::transaction(function () use ($playlist, $attributes) {
            $playlist->updateOrFail($attributes);

            return $playlist;
        });
    }
}
