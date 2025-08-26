<?php

declare(strict_types=1);

namespace Domain\Playlists\Listeners;

use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Domain\Playlists\Jobs\SyncProgress;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PlaylistProgressListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var int
     */
    public $timeout = 60 * 5;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    public function handle(PlaylistHasBeenViewedEvent $event): void
    {
        SyncProgress::dispatch($event->playlist, $event->user, $event->attributes);
    }
}
