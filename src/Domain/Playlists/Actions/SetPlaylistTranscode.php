<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use Illuminate\Support\Facades\DB;

class SetPlaylistTranscode
{
    public function handle(Playlist $playlist, array $attributes = []): mixed
    {
        return DB::transaction(function () use ($playlist, $attributes) {
            $playlist->updateQuietly(['progress' => $attributes]);

            return $playlist;
        });
    }
}
