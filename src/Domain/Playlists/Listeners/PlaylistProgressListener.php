<?php

declare(strict_types=1);

namespace Domain\Playlists\Listeners;

use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Domain\Playlists\Jobs\SyncProgress;

class PlaylistProgressListener
{
    public function handle(PlaylistHasBeenViewedEvent $event): void
    {
        SyncProgress::dispatch($event->playlist, $event->user, $event->attributes);
    }
}
