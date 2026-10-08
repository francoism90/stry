<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\PlaybackSettings;
use Illuminate\Support\Number;

class GetVideoProgress
{
    public function __construct(
        protected PlaybackSettings $settings,
    ) {}

    public function handle(Video $video, ?User $user = null): int|float
    {
        if (! $user || $video->duration <= 0) {
            return 0;
        }

        $progressKey = 'progress';

        if (! $video->modelCacheHas($progressKey)) {
            $record = $user->groupFor(GroupType::Viewed)->getGroupable($video);

            $time = (float) data_get($record->options ?? [], 'time', 0);

            return $this->fromTime($video, $time);
        }

        return $this->fromTime($video, (float) $video->modelCached($progressKey, 0));
    }

    /**
     * Share of the video a watch position covers, between 0 and 1, or 0 when the duration is unknown.
     */
    public function watchedFraction(Video $video, float $time): float
    {
        $duration = (float) $video->duration;

        if ($duration <= 0) {
            return 0.0;
        }

        return round(Number::clamp($time / $duration, 0, 1), 4);
    }

    /**
     * Turn a stored watch position into resumable progress: clamped to the duration, and 0 once
     * the video counts as watched.
     */
    public function fromTime(Video $video, ?float $time = null): float
    {
        $duration = (float) $video->duration;

        if ($duration <= 0) {
            return 0;
        }

        // Round the progress to 2 decimal places for consistency
        $time = Number::clamp(round($time ?? 0, 2), 0, $duration);

        // Past the completion threshold the video counts as fully watched (0 progress)
        if (($time / $duration) >= $this->settings->completion_threshold) {
            return 0.0;
        }

        return $time;
    }
}
