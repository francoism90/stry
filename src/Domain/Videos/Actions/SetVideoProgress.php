<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

class SetVideoProgress
{
    public function handle(Video $video, ?User $user = null, ?array $attributes = null): void
    {
        if (! $user || $video->duration <= 0) {
            return;
        }

        // Generate a unique cache key for the user's video progress
        $cacheKey = $user->generateCacheKey("video:progress:{$video->getKey()}");

        // Extract the current progress time from attributes (if provided)
        $time = $this->normalizeProgress($video, $attributes);

        // Only mark as viewed if the user hasn't already viewed the video
        if (Cache::missing($cacheKey) || ! $user->isInGroup($video, GroupType::Viewed)) {
            $user->markInGroup($video, GroupType::Viewed, ['time' => $time]);
        }

        // Always cache the progress for quick retrieval
        Cache::put($cacheKey, $time, now()->addHour());
    }

    protected function normalizeProgress(Video $video, ?array $attributes = null): float
    {
        // Extract the current progress time from attributes (if provided)
        $value = (float) data_get($attributes ?? [], 'time', 0);

        // Round the progress to 2 decimal places for consistency
        $time = round($value ?? 0, 2);

        return Number::clamp($time, 0, $video->duration ?? 0);
    }
}
