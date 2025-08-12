<?php

declare(strict_types=1);

namespace Domain\Playlists\Listeners;

use Domain\Playlists\Actions\UpdatePlaylistActivity;
use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

class PlaylistActivityListener implements ShouldQueueAfterCommit
{
    public function handle(PlaylistHasBeenViewedEvent $event): void
    {
        app(UpdatePlaylistActivity::class)->handle($event->playlist);
    }
}
