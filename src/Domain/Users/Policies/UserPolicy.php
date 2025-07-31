<?php

declare(strict_types=1);

namespace Domain\Users\Policies;

use Domain\Users\Models\User;
use Domain\Users\Models\User as UserModel;

class UserPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, UserModel $model): bool
    {
        return $user->is($model);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, UserModel $model): bool
    {
        return $user->is($model);
    }

    public function delete(User $user, UserModel $model): bool
    {
        return false;
    }

    public function restore(User $user, UserModel $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, UserModel $model): bool
    {
        return false;
    }
}
