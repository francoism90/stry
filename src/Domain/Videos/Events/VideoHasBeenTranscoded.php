<?php

declare(strict_types=1);

namespace Domain\Videos\Events;

use Domain\Videos\Models\Video;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoHasBeenTranscoded implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Video $video) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('videos.'.$this->video->getRouteKey());
    }

    public function broadcastAs(): string
    {
        return 'video.updated';
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
