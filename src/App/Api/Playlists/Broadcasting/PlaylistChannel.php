<?php

declare(strict_types=1);

namespace App\Api\Playlists\Broadcasting;

use Domain\Playlists\Models\Playlist;
use Domain\Users\Models\User;

class PlaylistChannel
{
    public function join(User $user, Playlist $playlist): bool
    {
        return $user->can('view', $playlist);
    }
}
