<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Number;

class SetVideoProgress
{
    public function handle(Video $video, ?User $user = null, ?array $attributes = null): void
    {
        if (! $user || $video->duration <= 0) {
            return;
        }

        // Cache the viewed time and progress for the video
        $viewedKey = 'viewed';

        $progressKey = 'progress';

        // Normalize the progress time to a float between 0 and the video duration
        $time = $this->normalizeProgress($video, $attributes);

        // Mark the video as viewed if the user is in the viewed group, or mark them as viewing if not
        if (! $video->modelCacheHas($viewedKey)) {
            $user->markInGroup($video, GroupType::Viewed, ['time' => $time]);
        }

        // Update the progress time in the cache for the video
        $video->modelCache($viewedKey, $time, now()->addMinutes(30));

        $video->modelCache($progressKey, $time, now()->addWeek());
    }

    protected function normalizeProgress(Video $video, ?array $attributes = null): float
    {
        // Extract the current progress time from attributes (if provided)
        $value = (float) data_get($attributes ?? [], 'time', 0);

        // Round the progress to 2 decimal places for consistency
        $time = round($value ?? 0, 2);

        return Number::clamp($time, 0, $video->duration ?? 0);
    }
}
