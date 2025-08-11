<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Jobs\PlaylistVideo;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class VideoViewedListener implements ShouldQueueAfterCommit
{
    public function handle(VideoHasBeenViewedEvent $event): void
    {
        PlaylistVideo::dispatch($event->video);
    }
}
