<?php

declare(strict_types=1);

namespace Domain\Playlists\Policies;

use Domain\Playlists\Models\Playlist;
use Domain\Users\Models\User;

class PlaylistPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Playlist $playlist): bool
    {
        return $playlist->isProcessed();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Playlist $playlist): bool
    {
        return $playlist->user()->is($user);
    }

    public function delete(User $user, Playlist $playlist): bool
    {
        return $playlist->user()->is($user);
    }

    public function restore(User $user, Playlist $playlist): bool
    {
        return $playlist->user()->is($user);
    }

    public function forceDelete(User $user, Playlist $playlist): bool
    {
        return false;
    }
}
