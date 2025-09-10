<?php

declare(strict_types=1);

namespace Domain\Groups\Policies;

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;

class GroupPolicy
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

    public function view(User $user, Group $group): bool
    {
        return $group->user()->is($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Group $group): bool
    {
        return $group->user()->is($user);
    }

    public function delete(User $user, Group $group): bool
    {
        return $group->user()->is($user);
    }

    public function restore(User $user, Group $group): bool
    {
        return $group->user()->is($user);
    }

    public function forceDelete(User $user, Group $group): bool
    {
        return false;
    }
}
