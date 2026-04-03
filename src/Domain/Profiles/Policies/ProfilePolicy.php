<?php

declare(strict_types=1);

namespace Domain\Profiles\Policies;

use Domain\Profiles\Models\Profile;
use Domain\Users\Models\User;

class ProfilePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Profile $profile): bool
    {
        return $profile->user()->is($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Profile $profile): bool
    {
        return $profile->user()->is($user);
    }

    public function delete(User $user, Profile $profile): bool
    {
        return $this->update($user, $profile);
    }

    public function restore(User $user, Profile $profile): bool
    {
        return $this->update($user, $profile);
    }

    public function forceDelete(User $user, Profile $profile): bool
    {
        return false;
    }
}
