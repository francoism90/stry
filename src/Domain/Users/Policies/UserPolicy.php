<?php

declare(strict_types=1);

namespace Domain\Users\Policies;

use Domain\Users\Models\User;
use Domain\Users\Models\User as UserModel;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function view(User $user, UserModel $model): bool
    {
        return $user->is($model) || $user->hasRole('super-admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super-admin');
    }

    public function update(User $user, UserModel $model): bool
    {
        return $user->is($model) || $user->hasRole('super-admin');
    }

    public function delete(User $user, UserModel $model): bool
    {
        return $user->hasRole('super-admin');
    }

    public function restore(User $user, UserModel $model): bool
    {
        return $user->hasRole('super-admin');
    }

    public function forceDelete(User $user, UserModel $model): bool
    {
        return $user->hasRole('super-admin');
    }
}
