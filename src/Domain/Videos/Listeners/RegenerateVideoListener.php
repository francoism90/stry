<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Events\VideoHasBeenAddedEvent;
use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Domain\Videos\Jobs\ProcessVideo;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class RegenerateVideoListener implements ShouldQueueAfterCommit
{
    public function handle(VideoHasBeenAddedEvent|VideoHasBeenUpdatedEvent $event): void
    {
        ProcessVideo::dispatch($event->video);
    }
}
