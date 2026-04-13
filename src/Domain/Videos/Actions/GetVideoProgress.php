<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Number;

class GetVideoProgress
{
    public function handle(Video $video, ?User $user = null): int|float
    {
        if (! $user || $video->duration <= 0) {
            return 0;
        }

        $progressKey = 'progress';

        if (! $video->modelCacheHas($progressKey)) {
            $record = $user->groupFor(GroupType::Viewed)->getGroupable($video);

            $time = (float) data_get($record?->pivot?->options ?? [], 'time', 0);

            return $this->normalizeProgress($video, $time);
        }

        return $this->normalizeProgress($video, (float) $video->modelCached($progressKey, 0));
    }

    protected function normalizeProgress(Video $video, ?float $time = null): float
    {
        $duration = (float) $video->duration ?? 0;

        if ($duration <= 0) {
            return 0;
        }

        // Round the progress to 2 decimal places for consistency
        $time = Number::clamp(round($time ?? 0, 2), 0, $duration);

        // If the progress is 95% or more of the video duration, consider it as fully watched (0 progress)
        if (($time / $duration) >= Video::getCompletionThreshold()) {
            return 0.0;
        }

        return $time;
    }
}
