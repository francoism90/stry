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
    public function handle(Video $video, User $user, float $seconds = 0): Video
    {
        return DB::transaction(function () use ($video, $user, $seconds) {
            // Update the video progress
            Cache::put($this->getCacheKey($video, $user), round($seconds, 2), now()->addDay());

            // Set video analytics
            TrackVideo::dispatch($video, $user, ['time' => $seconds]);

            return $video;
        });
    }

    protected function getCacheKey(Video $video, User $user): string
    {
        return hash('xxh128', implode(':', ['progress', $video->getKey(), $user->getKey()]));
    }
}
