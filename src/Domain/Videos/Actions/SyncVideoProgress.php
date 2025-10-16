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
            // This will be used to throttle video progress tracking
            $cacheKey = $this->getCacheKey($video, $user);

            // Dispatch the job to track video progress if not recently dispatched
            TrackVideo::dispatchIf(
                Cache::missing($cacheKey),
                $video, $user, $attributes
            );

            // Cache the attributes for 24 hours to prevent multiple job dispatches
            Cache::put($cacheKey, $attributes, now()->addDay());

            return $video;
        });
    }

    protected function getCacheKey(Video $video, User $user): string
    {
        return hash('xxh128', implode(':', ['progress', $video->getKey(), $user->getKey()]));
    }
}
