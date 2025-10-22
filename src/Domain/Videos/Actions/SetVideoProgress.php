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
            // This will be used to throttle video viewed tracking
            $cacheKey = $user->getCacheKey("video-progress-{$video->getKey()}");

            // Mark the video as viewed if not recently tracked
            if (Cache::missing($cacheKey)) {
                app(MarkVideoAsViewed::class)->handle($video, $user, $attributes);
            }

            // Cache the progress to avoid excessive updates
            Cache::put($cacheKey, $attributes, now()->addMinutes(30));
        });
    }
}
