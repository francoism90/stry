<?php

declare(strict_types=1);

namespace Domain\Tags\Policies;

use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;

class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tag $tag): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, Tag $tag): bool
    {
        return $tag->user()->is($user) || $user->hasRole('super-admin');
    }

    public function delete(User $user, Tag $tag): bool
    {
        return $tag->user()->is($user) || $user->hasRole('super-admin');
    }

    public function restore(User $user, Tag $tag): bool
    {
        return $user->hasRole('super-admin');
    }

    public function forceDelete(User $user, Tag $tag): bool
    {
        return $user->hasRole('super-admin');
    }
}
