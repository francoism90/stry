<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Actions\MarkVideoAsVerified;
use Domain\Videos\Events\VideoHasBeenAddedEvent;

class SendVideoHasBeenProcessed
{
    public function handle(VideoHasBeenAddedEvent $event): void
    {
        app(MarkVideoAsVerified::class)->handle($event->video);
    }
}
