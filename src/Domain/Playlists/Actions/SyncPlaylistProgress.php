<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Playlists\Models\Playlist;
use Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;

class SyncPlaylistProgress
{
    public function handle(Playlist $playlist, ?User $user = null, ?array $attributes = null): mixed
    {
        return DB::transaction(function () use ($playlist, $user, $attributes) {
            if (! $user || ! $model = $playlist->getModel()) {
                return;
            }

            // Ensure the user has a viewed group
            $group = $user->findOrCreateGroup(GroupType::Viewed);

            // Update with the playlist attributes
            $model->syncGroup($group, $attributes);
        });
    }
}
