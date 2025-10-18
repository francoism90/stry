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
            $cacheKey = $this->getCacheKey($video, $user);

            // Mark the video as viewed if not recently tracked
            if (Cache::missing($cacheKey)) {
                app(MarkVideoAsViewed::class)->handle($video, $user, $attributes);
            }

            // Cache the progress to avoid excessive updates
            Cache::put($cacheKey, $attributes, now()->addMinutes(30));
        });
    }

    protected function getCacheKey(Video $video, User $user): string
    {
        return hash('xxh128', implode(':', ['progress', $video->getKey(), $user->getKey()]));
    }
}
