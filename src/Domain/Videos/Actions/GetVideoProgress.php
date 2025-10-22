<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Cache;

class GetVideoProgress
{
    public function handle(Video $video, User $user): int|float
    {
        // Find by cache first
        $cacheKey = $user->getCacheKey("video-progress-{$video->getKey()}");

        if (Cache::has($cacheKey)) {
            return round(data_get(Cache::get($cacheKey, []), 'time') ?: 0, 2);
        }

        // Ensure the user has a viewed group
        $group = $user->findOrCreateGroup(GroupType::Viewed);

        // Find the video in the viewed group
        $record = $group->videos()->find($video);

        // Get the progress record for the video
        return round(data_get($record?->pivot?->options ?? [], 'time') ?: 0, 2);
    }
}
