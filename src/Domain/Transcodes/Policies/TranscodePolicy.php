<?php

declare(strict_types=1);

namespace Domain\Transcodes\Policies;

use Domain\Transcodes\Models\Transcode;
use Domain\Users\Models\User;

class TranscodePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Transcode $transcode): bool
    {
        return $transcode->user()->is($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Transcode $transcode): bool
    {
        return $transcode->user()->is($user);
    }

    public function delete(User $user, Transcode $transcode): bool
    {
        return $this->update($user, $transcode);
    }

    public function restore(User $user, Transcode $transcode): bool
    {
        return $this->update($user, $transcode);
    }

    public function forceDelete(User $user, Transcode $transcode): bool
    {
        return false;
    }
}
