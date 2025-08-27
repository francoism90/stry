<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Auth;

class GetVideoProgress
{
    public function handle(Video $video): float
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return 0;
        }

        // Ensure the user has a viewed group
        $group = $user->findOrCreateGroup(GroupType::Viewed);

        // Check if the video is in the viewed group
        $record = $group->videos()->find($video);

        // Find the progress record for the video
        $progress = data_get($record?->pivot?->options, 'time');

        return round(floatval($progress ?: 0), 2);
    }
}
