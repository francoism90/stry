<?php

declare(strict_types=1);

namespace Domain\Groups\Policies;

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;

class GroupPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Group $group): bool
    {
        return $group->user()->is($user) || $user->hasRole('super-admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, Group $group): bool
    {
        return $group->user()->is($user) || $user->hasRole('super-admin');
    }

    public function delete(User $user, Group $group): bool
    {
        return $group->user()->is($user) || $user->hasRole('super-admin');
    }

    public function restore(User $user, Group $group): bool
    {
        return $group->user()->is($user) || $user->hasRole('super-admin');
    }

    public function forceDelete(User $user, Group $group): bool
    {
        return $user->hasRole('super-admin');
    }
}
