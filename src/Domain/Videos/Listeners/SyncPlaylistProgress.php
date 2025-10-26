<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Actions\SetVideoProgress;
use Domain\Videos\Events\VideoHasBeenViewedEvent;

class SyncPlaylistProgress
{
    public function handle(VideoHasBeenViewedEvent $event): void
    {
        app(SetVideoProgress::class)->handle($event->video, $event->user, $event->attributes);
    }
}
