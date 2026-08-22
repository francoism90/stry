<?php

declare(strict_types=1);

namespace Domain\Videos\Policies;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

class VideoPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Video $video): bool
    {
        return $video->isValid();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Video $video): bool
    {
        return $video->user()->is($user);
    }

    public function delete(User $user, Video $video): bool
    {
        return $this->update($user, $video);
    }

    public function restore(User $user, Video $video): bool
    {
        return $this->update($user, $video);
    }

    public function forceDelete(User $user, Video $video): bool
    {
        return false;
    }
}
