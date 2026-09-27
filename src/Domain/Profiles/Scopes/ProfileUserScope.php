<?php

declare(strict_types=1);

namespace Domain\Profiles\Scopes;

use Domain\Profiles\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Builder;

readonly class ProfileUserScope
{
    /**
     * @param  Builder<Profile>  $scout
     */
    public function __invoke(Builder $scout): void
    {
        $userId = Auth::id();

        if (blank($userId)) {
            $scout->where('user_id', '__no_user__');

            return;
        }

        $scout->where('user_id', (string) $userId);
    }
}
