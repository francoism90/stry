<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SetVideoProgress
{
    public function __construct(
        public MarkVideoAsViewed $viewed,
    ) {}

    public function handle(Video $video, User $user, ?array $attributes = null): mixed
    {
        return DB::transaction(function () use ($video, $user, $attributes) {
            // Generate a unique cache key for the user's video progress
            $cacheKey = $user->generateCacheKey("progress:{$video->getKey()}");

            // Extract the current progress time from attributes
            $time = (float) data_get($attributes, 'time', 0);

            // Store the video as viewed if not already cached
            if (Cache::missing($cacheKey) && $time >= 0) {
                $this->viewed->handle($video, $user, $attributes);
            }

            // Get the current cached progress time
            $current = (float) Cache::get($cacheKey, 0);

            // Cache the progress to avoid excessive updates
            if ($time >= 0 && $current !== $time) {
                Cache::put($cacheKey, $time, now()->addDays(3));
            }
        });
    }
}
