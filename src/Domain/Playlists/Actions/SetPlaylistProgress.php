<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Playlists\Models\Playlist;
use Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

class SetPlaylistProgress
{
    public function handle(Playlist $playlist, User $user, ?array $attributes = null): Playlist
    {
        return DB::transaction(function () use ($playlist, $user, $attributes) {
            if (! $model = $playlist->getModel()) {
                return $playlist;
            }

            // Ensure the user has a viewed group
            $group = $user->findOrCreateGroup(GroupType::Viewed);

            // Update with the playlist attributes
            $model->syncGroup($group, $attributes);

            return $playlist;
        });
    }
}
