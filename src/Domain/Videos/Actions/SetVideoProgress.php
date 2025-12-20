<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
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
            $cacheKey = $user->generateCacheKey("progress:{$video->getKey()}");

            // Extract the current progress time from attributes
            $time = (float) data_get($attributes, 'time', 0);

            // Store the video as viewed if not already cached
            if (Cache::missing($cacheKey) && $time > 0) {
                // Ensure the user has a viewed group
                $group = $user->findOrCreateGroup(GroupType::Viewed);

                // Update with the video attributes
                $video->syncGroup($group, $attributes);
            }

            // Get the current progress time (if any)
            $current = (float) Cache::get($cacheKey, 0);

            // Update the cache only if the time has changed
            if ($current !== $time) {
                Cache::put($cacheKey, $time, now()->addMinutes(30));
            }
        });
    }
}
