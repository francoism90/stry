<?php

declare(strict_types=1);

namespace Domain\Videos\Events;

use Domain\Videos\Models\Video;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoHasBeenViewedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Video $video,
    ) {}
}
