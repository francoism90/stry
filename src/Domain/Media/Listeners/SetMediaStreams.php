<?php

declare(strict_types=1);

namespace Domain\Media\Listeners;

use Domain\Media\Actions\ParseMediaStreams;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class SetMediaStreams implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    /**
     * @var string|null
     */
    public $queue = 'processing';

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var int
     */
    public $timeout = 60 * 3;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $streams = app(ParseMediaStreams::class)->handle($event->media);

        if ($streams->isNotEmpty()) {
            $event->media->setCustomProperty('streams', $streams->toArray());
            $event->media->saveOrFail();
        }
    }
}
