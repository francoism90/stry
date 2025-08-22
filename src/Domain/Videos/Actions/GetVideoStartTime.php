<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Actions\CreateUserGroup;
use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

class GetVideoStartTime
{
    public function handle(Video $video, ?User $user = null): float
    {
        if (! $user || ! $video->hasMedia('clips')) {
            return 0;
        }

        // Ensure the user viewed group exists
        $group = app(CreateUserGroup::class)->handle($user, GroupType::Viewed);

        // Find the video in the user viewed group (if exists)
        $videoable = $group->videos()->find($video);

        return round($videoable?->pivot?->options?->time ?: 0, 2);
    }
}
