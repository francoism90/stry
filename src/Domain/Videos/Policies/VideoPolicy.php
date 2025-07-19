<?php

declare(strict_types=1);

namespace Domain\Videos\Policies;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

class VideoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Video $video): bool
    {
        return $video->user()->is($user) || $user->hasRole('super-admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, Video $video): bool
    {
        return $video->user()->is($user) || $user->hasRole('super-admin');
    }

    public function delete(User $user, Video $video): bool
    {
        return $video->user()->is($user) || $user->hasRole('super-admin');
    }

    public function restore(User $user, Video $video): bool
    {
        return $video->user()->is($user) || $user->hasRole('super-admin');
    }

    public function forceDelete(User $user, Video $video): bool
    {
        return $user->hasRole('super-admin');
    }
}
