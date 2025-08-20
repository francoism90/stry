<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Jobs\PlaylistVideo;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class GeneratePlaylistsListener implements ShouldQueueAfterCommit
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

    public function handle(VideoHasBeenViewedEvent $event): void
    {
        PlaylistVideo::dispatch($event->video);
    }
}
