<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class MarkVideoAsSaved
{
    public function execute(User $user, Video $video, ?bool $force = null): void
    {
        DB::transaction(function () use ($user, $video, $force) {
            $group = $user->groups()->saves()->first();

            if (! $group instanceof Group) {
                return;
            }

            // Toggle state
            $force === true || ! $video->isSavedBy($user)
                ? $video->attachGroup($group)
                : $video->detachGroup($group);

            // Touch parent to trigger broadcast
            $group->touch();
        });
    }
}
