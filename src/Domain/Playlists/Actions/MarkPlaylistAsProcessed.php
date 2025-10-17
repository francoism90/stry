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
            // Transition the playlist state if possible
            if ($playlist->state->canTransitionTo(Verified::class)) {
                $playlist->state->transitionTo(Verified::class);
            }

            // Update the transcoded_at timestamp if not already set
            if (blank($playlist->transcoded_at)) {
                $playlist->touch('transcoded_at');
            }
        });
    }
}
