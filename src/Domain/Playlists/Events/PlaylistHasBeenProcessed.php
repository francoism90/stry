<?php

declare(strict_types=1);

namespace Domain\Playlists\Events;

use Domain\Playlists\Models\Playlist;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaylistHasBeenProcessed implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Playlist $playlist,
    ) {}

    /**
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel($this->playlist),
            new PrivateChannel($this->playlist->getModel()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'playlist.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->playlist->getRouteKey(),
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
