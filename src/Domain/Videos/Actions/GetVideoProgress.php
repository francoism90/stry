<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Number;

class GetVideoProgress
{
    public function handle(Video $video, ?User $user = null): int|float
    {
        // If there is no user, return 0 progress
        if (! $user || $video->duration <= 0) {
            return 0;
        }

        // Get the existing progress record
        $progress = (float) $user->cachedValue("video:progress:{$video->getKey()}", 0);

        if ($progress <= 0) {
            // Find the video in the viewed group
            $record = $user->viewedGroup()->getGroupable($video);

            // Extract the progress time from options
            $progress = data_get($record?->pivot?->options ?? [], 'time', 0);
        }

        return Number::clamp(round($progress, 2), 0, $video->duration);
    }
}
