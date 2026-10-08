<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\Actions\GetVideoProgress;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Support\Number;

class SyncVideoProgress
{
    /**
     * Seconds the position has to move before it is written to the viewed group again.
     */
    protected const int PersistInterval = 15;

    public function __construct(
        protected GetVideoProgress $getVideoProgress,
    ) {}

    public function handle(VideoHasBeenViewedEvent $event): void
    {
        $video = $event->video;
        $user = $event->user;

        if (! $user) {
            return;
        }

        // Get the cache key for the position last written to the viewed group
        $viewedKey = GroupType::Viewed->value;

        // Normalize the progress time to a float between 0 and the video duration
        $time = $this->normalizeProgress($video, $event->attributes);

        $persistedTime = $video->modelCached($viewedKey);

        // Lists and the "In progress" filter read the viewed group, so keep it close to the player
        // without writing (and re-indexing) on every player update
        if ($this->shouldPersist($video, $time, $persistedTime)) {
            $user->markInGroup($video, GroupType::Viewed, [
                'time' => $time,
                'progress' => $this->getVideoProgress->watchedFraction($video, $time),
            ]);

            $video->modelCache($viewedKey, $time, now()->addHour());
        }

        // Cache the progress time for the video for 1 week
        $video->modelCache('progress', $time, now()->addWeek());
    }

    protected function shouldPersist(Video $video, float $time, mixed $persistedTime): bool
    {
        if (! is_numeric($persistedTime)) {
            return true;
        }

        $persistedTime = (float) $persistedTime;

        if (abs($time - $persistedTime) >= static::PersistInterval) {
            return true;
        }

        // Finishing (or rewinding a finished video) changes what lists show, however far it moved
        return $this->isWatched($video, $time) !== $this->isWatched($video, $persistedTime);
    }

    protected function isWatched(Video $video, float $time): bool
    {
        return $time > 0 && $this->getVideoProgress->fromTime($video, $time) === 0.0;
    }

    /**
     * @param  array<string, mixed>|null  $attributes
     */
    protected function normalizeProgress(Video $video, ?array $attributes = null): float
    {
        // Extract the current progress time from attributes (if provided)
        $value = (float) data_get($attributes ?? [], 'time', 0);

        // Round the progress to 2 decimal places for consistency
        $time = round($value, 2);

        return Number::clamp($time, 0, $video->duration ?? 0);
    }
}
