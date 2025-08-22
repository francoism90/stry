<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Jobs\PlaylistVideo;

class GeneratePlaylistsListener
{
    public function handle(VideoHasBeenViewedEvent $event): void
    {
        PlaylistVideo::dispatch($event->video);
    }
}
