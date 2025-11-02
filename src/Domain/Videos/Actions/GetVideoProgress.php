<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

class GetVideoProgress
{
    public function handle(Video $video, ?User $user = null): int|float
    {
        // If there is no user, return 0 progress
        if (! $user) {
            return 0;
        }

        // Generate a unique cache key for the user's video progress
        $cacheSuffix = "progress:{$video->getKey()}";

        // Get the existing progress record
        $current = (float) $user->getCacheValue($cacheSuffix, 0);

        // Return the cached progress if it exists
        if ($current > 0) {
            return round($current, 2);
        }

        // If not cached, try to retrieve from the database
        $group = $user->findOrCreateGroup(GroupType::Viewed);

        // Find the video in the viewed group
        $record = $group->videos()->find($video);

        // Extract the progress time from the pivot options
        $current = data_get($record?->pivot?->options ?? [], 'time', 0);

        // Return the progress rounded to two decimal places
        return round($current ?: 0, 2);
    }
}
