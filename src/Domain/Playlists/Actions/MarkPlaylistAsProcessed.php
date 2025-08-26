<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use Domain\Playlists\States\Verified;
use Illuminate\Support\Facades\DB;

class MarkPlaylistAsProcessed
{
    public function handle(Playlist $playlist): mixed
    {
        return DB::transaction(function () use ($playlist) {
            // Set state to verified if it can transition
            if ($playlist->state->canTransitionTo(Verified::class)) {
                $playlist->state->transitionTo(Verified::class);
            }

            // Mark the playlist as transcoded
            $playlist->touch('transcoded_at');

            return $playlist;
        });
    }
}
