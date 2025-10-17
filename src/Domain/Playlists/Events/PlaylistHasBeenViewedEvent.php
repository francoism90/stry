<?php

declare(strict_types=1);

namespace Domain\Playlists\Events;

use Domain\Playlists\Models\Playlist;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaylistHasBeenViewedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Playlist $playlist,
    ) {}
}
