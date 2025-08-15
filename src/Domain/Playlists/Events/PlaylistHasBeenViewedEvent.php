<?php

declare(strict_types=1);

namespace Domain\Playlists\Events;

use Domain\Playlists\Models\Playlist;
use Domain\Users\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaylistHasBeenViewedEvent implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Playlist $playlist,
        public ?User $user = null,
        public ?array $attributes = null,
    ) {}
}
