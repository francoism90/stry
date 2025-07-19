<?php

declare(strict_types=1);

namespace Domain\Users\Events;

use Domain\Users\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserHasBeenReleased implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public User $user) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('users.'.$this->user->getRouteKey());
    }

    public function broadcastAs(): string
    {
        return 'user.updated';
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}
