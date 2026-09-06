<?php

declare(strict_types=1);

namespace Domain\Chapters\Policies;

use Domain\Chapters\Models\Chapter;
use Domain\Users\Models\User;

class ChapterPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Chapter $chapter): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Chapter $chapter): bool
    {
        return $chapter->video->user()->is($user);
    }

    public function delete(User $user, Chapter $chapter): bool
    {
        return $this->update($user, $chapter);
    }

    public function restore(User $user, Chapter $chapter): bool
    {
        return $this->update($user, $chapter);
    }

    public function forceDelete(User $user, Chapter $chapter): bool
    {
        return false;
    }
}
