<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;

class GetVideoProgress
{
    public function handle(Video $video, User $user): float
    {
        $cacheKey = $this->getCacheKey($video, $user);

        return (float) Cache::remember($cacheKey, now()->addHours(4), function () use ($video, $user) {
            // Ensure the user has a viewed group
            $group = $user->findOrCreateGroup(GroupType::Viewed);

            // Find the video in the viewed group
            $record = $group->videos()->find($video);

            // Get the progress record for the video
            $progress = data_get($record?->pivot?->options ?? [], 'time');

            return round($progress ?: 0, 2);
        });
    }

    protected function getCacheKey(Video $video, User $user): string
    {
        return hash('xxh128', implode(':', ['progress', $video->getKey(), $user->getKey()]));
    }
}
