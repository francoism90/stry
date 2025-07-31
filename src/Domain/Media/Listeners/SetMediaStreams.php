<?php

declare(strict_types=1);

namespace Domain\Media\Listeners;

use Domain\Media\Actions\ParseMediaStreams;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class SetMediaStreams
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $streams = app(ParseMediaStreams::class)->handle($event->media);

        if ($streams->isNotEmpty()) {
            $event->media->setCustomProperty('streams', $streams->toArray());
            $event->media->saveOrFail();
        }
    }
}
