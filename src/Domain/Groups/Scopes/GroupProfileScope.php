<?php

declare(strict_types=1);

namespace Domain\Groups\Scopes;

use Domain\Profiles\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Builder;

readonly class GroupProfileScope
{
    public function __invoke(Builder $scout): void
    {
        // TODO: This is a temporary solution to filter groups by the current user's profile.
        $userId = Profile::current()?->user_id ?? Auth::id();

        if (blank($userId)) {
            $scout->where('user_id', '__no_user__');

            return;
        }

        $scout->where('user_id', (string) $userId);
    }
}
