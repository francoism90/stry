<?php

declare(strict_types=1);

namespace Domain\Playlists\Listeners;

use Domain\Playlists\Actions\RefreshPlaylistManifest;
use Domain\Videos\Events\VideoHasBeenViewedEvent;

class RefreshExpiringPlaylistManifest
{
    public function __construct(protected RefreshPlaylistManifest $refreshPlaylistManifest) {}

    public function handle(VideoHasBeenViewedEvent $event): void
    {
        if ($playlist = $event->video->getPlaylist()) {
            $this->refreshPlaylistManifest->handle($playlist);
        }
    }
}
