<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Domain\Videos\Actions\SetVideoProgress;

class SyncPlaylistProgress
{
    public function handle(PlaylistHasBeenViewedEvent $event): void
    {
        if (! $model = $event->playlist->getModel()) {
            return;
        }

        app(SetVideoProgress::class)->handle(
            video: $model,
            user: $event->user,
            attributes: $event->attributes,
        );
    }
}
