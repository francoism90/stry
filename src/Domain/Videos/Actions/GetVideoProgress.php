<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

class GetVideoProgress
{
    public function handle(Video $video, ?User $user = null): int|float
    {
        // If there is no user, return 0 progress
        if (! $user || $video->duration <= 0) {
            return 0;
        }

        // Generate a unique cache key for the user's video progress
        $cacheKey = $user->generateCacheKey("video:progress:{$video->getKey()}");

        // If there is no cached progress, try to get it from the viewed records
        if (Cache::missing($cacheKey)) {
            // Attempt to retrieve the viewed record for the video
            $record = $user->groupFor(GroupType::Viewed)->getGroupable($video);

            // Extract the progress time from the pivot options, defaulting to 0 if not available
            $time = (float) data_get($record?->pivot?->options ?? [], 'time', 0);

            return $this->normalizeProgress($video, $time);
        }

        // Get the cached progress time, defaulting to 0 if not found
        return $this->normalizeProgress($video, Cache::float($cacheKey, 0));
    }

    protected function normalizeProgress(Video $video, ?float $time = null): float
    {
        $duration = (float) $video->duration ?? 0;

        if ($duration <= 0) {
            return 0;
        }

        // Round the progress to 2 decimal places for consistency
        $time = Number::clamp(round($time ?? 0, 2), 0, $duration);

        // If the progress is 95% or more of the video duration, consider it as fully watched (0 progress)
        if (($time / $duration) >= Video::getCompletionThreshold()) {
            return 0.0;
        }

        return $time;
    }
}
