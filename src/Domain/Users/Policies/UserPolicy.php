<?php

declare(strict_types=1);

namespace Domain\Users\Policies;

use Domain\Users\Models\User;
use Domain\Users\Models\User as Model;

class UserPolicy
{
    public function before(User $user, string $ability, mixed $model = null): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // An admin can't create users, or act on a super-admin, which would be a way to become one
        if (! $user->isAdmin() || $ability === 'create') {
            return null;
        }

        if ($model instanceof Model && $model->isSuperAdmin()) {
            return null;
        }

        return true;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Model $model): bool
    {
        return $user->is($model);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->is($model);
    }

    public function delete(User $user, Model $model): bool
    {
        return false;
    }

    public function restore(User $user, Model $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return false;
    }
}
