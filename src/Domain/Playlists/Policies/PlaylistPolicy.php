<?php

declare(strict_types=1);

namespace Domain\Playlists\Policies;

use Domain\Playlists\Models\Playlist;
use Domain\Users\Models\User;

class PlaylistPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Playlist $playlist): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, Playlist $playlist): bool
    {
        return $playlist->user()->is($user) || $user->hasRole('super-admin');
    }

    public function delete(User $user, Playlist $playlist): bool
    {
        return $playlist->user()->is($user) || $user->hasRole('super-admin');
    }

    public function restore(User $user, Playlist $playlist): bool
    {
        return $playlist->user()->is($user) || $user->hasRole('super-admin');
    }

    public function forceDelete(User $user, Playlist $playlist): bool
    {
        return $user->hasRole('super-admin');
    }
}
