<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SetVideoProgress
{
    public function handle(Video $video, User $user, ?array $attributes = null): mixed
    {
        return DB::transaction(function () use ($video, $user, $attributes) {
            // Generate a unique cache key for the user's video progress
            $cacheKey = $user->getCacheKey("video-progress-{$video->getKey()}");

            // Extract the progress time from attributes
            $time = (float) data_get($attributes, 'time', 0);

            // If cache is missing and time is greater than 0, mark the video as viewed
            if (Cache::missing($cacheKey) && $time > 0) {
                app(MarkVideoAsViewed::class)->handle($video, $user, $attributes);
            }

            // Get the existing progress record (if any)
            $current = Cache::get($cacheKey, 0);

            // Cache the progress to avoid excessive updates
            if ($time > 0 && $current !== $time) {
                Cache::put($cacheKey, $time, now()->addMinutes(30));
            }
        });
    }
}
