<?php

declare(strict_types=1);

namespace Domain\Groups\Policies;

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;

class GroupPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Group $group): bool
    {
        return $group->user()->is($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Group $group): bool
    {
        return $group->isCustom() && $group->user()->is($user);
    }

    public function delete(User $user, Group $group): bool
    {
        return $group->isCustom() && $group->user()->is($user);
    }

    public function restore(User $user, Group $group): bool
    {
        return $group->isCustom() && $group->user()->is($user);
    }

    public function forceDelete(User $user, Group $group): bool
    {
        return false;
    }
}
