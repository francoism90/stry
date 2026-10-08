<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Domain\Users\Models\User;
use Domain\Videos\Actions\GetVideoProgress;

/**
 * Where each user left off in the video, stored as the watch position of their viewed group.
 */
trait InteractsWithProgress
{
    /**
     * Watch positions in seconds, keyed by user, resolved for a whole list at once.
     *
     * @var array<array-key, float>
     */
    protected array $watchTimesByUser = [];

    public function setWatchTimeFor(User $user, float $time): static
    {
        $this->watchTimesByUser[$user->getKey()] = $time;

        return $this;
    }

    /**
     * The position to resume from in seconds, or 0 when the video was never started or counts as watched.
     */
    public function progressOf(User $user): float
    {
        $getVideoProgress = app(GetVideoProgress::class);

        if (! array_key_exists($user->getKey(), $this->watchTimesByUser)) {
            return (float) $getVideoProgress->handle($this, $user);
        }

        return $getVideoProgress->fromTime($this, $this->watchTimesByUser[$user->getKey()]);
    }
}
