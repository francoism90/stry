<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ResetVideoProgress
{
    public function handle(Video $video, User $user): mixed
    {
        return DB::transaction(function () use ($video, $user) {
            // This will be used to identify the cached progress
            $cacheKey = $this->getCacheKey($video, $user);

            // Forget the cached progress
            return Cache::forget($cacheKey);
        });
    }

    protected function getCacheKey(Video $video, User $user): string
    {
        return hash('xxh128', implode(':', ['progress', $video->getKey(), $user->getKey()]));
    }
}
