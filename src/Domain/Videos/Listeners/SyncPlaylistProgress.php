<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Support\Number;

class SyncPlaylistProgress
{
    public function handle(VideoHasBeenViewedEvent $event): void
    {
        if (! ($video = $event->video) || ! ($user = $event->user)) {
            return;
        }

        // Get the cache key for the viewed group
        $viewedKey = GroupType::Viewed->value;

        // Normalize the progress time to a float between 0 and the video duration
        $time = $this->normalizeProgress($video, $event->attributes);

        // Mark the video as viewed if the user is in the viewed group, or mark them as viewing if not
        if (! $video->modelCacheHas($viewedKey)) {
            $user->markInGroup($video, GroupType::Viewed, ['time' => $time]);
        }

        // Cache the progress time for the video for 1 hour
        $video->modelCache($viewedKey, $time, now()->addHour());

        // Cache the progress time for the video for 1 week
        $video->modelCache('progress', $time, now()->addWeek());
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
