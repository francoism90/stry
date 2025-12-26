<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

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

        // Get the existing progress record
        $current = (float) $user->cachedValue("video:progress:{$video->getKey()}", 0);

        // Return the cached progress if it exists
        if ($current > 0) {
            return round($current, 2);
        }

        // Find the video in the viewed group
        $record = $user->viewedGroup()->getGroupable($video);

        // Extract the progress time from the pivot options
        $current = data_get($record?->pivot?->options ?? [], 'time', 0);

        // Return the progress rounded to two decimal places
        return round($current ?: 0, 2);
    }
}
