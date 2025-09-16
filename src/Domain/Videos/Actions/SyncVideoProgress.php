<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Jobs\TrackVideo;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncVideoProgress
{
    public function handle(Video $video, User $user, ?array $attributes = null): Video
    {
        return DB::transaction(function () use ($video, $user, $attributes) {
            // Generate a unique cache key
            $cacheKey = $this->getCacheKey($video, $user);

            // Video analytics tracking
            TrackVideo::dispatchIf(
                Cache::missing($cacheKey),
                $video, $user, $attributes
            );

            // Sync user video progress
            Cache::put($cacheKey, $attributes, now()->addDay());

            return $video;
        });
    }

    protected function getCacheKey(Video $video, User $user): string
    {
        return hash('xxh128', implode(':', ['progress', $video->getKey(), $user->getKey()]));
    }
}
