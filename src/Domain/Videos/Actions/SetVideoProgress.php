<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class SetVideoProgress
{
    public function handle(Video $video, User $user, ?array $attributes = null): mixed
    {
        return DB::transaction(function () use ($video, $user, $attributes) {
            // Generate a unique cache key for the user's video progress
            $cacheKey = $user->generateCacheKey("video:progress:{$video->getKey()}");

            // Extract the current progress time from attributes (if provided)
            $time = (float) data_get($attributes, 'time', 0);

            // Clamp the time between 0 and the video's duration
            $time = Number::clamp($time, 0, $video->duration);

            // Store the video as viewed if not already cached
            if (Cache::missing($cacheKey) || ! $user->isViewed($video)) {
                $user->markAsViewed($video, ['time' => $time]);
            }

            // Update the cached progress time
            Cache::put($cacheKey, $time, now()->addMinutes(30));
        });
    }
}
