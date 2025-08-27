<?php

declare(strict_types=1);

namespace Domain\Media\Listeners;

use Domain\Media\Actions\SetMediaStreams;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Pipeline;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ProcessMedia implements ShouldQueueAfterCommit
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
    public $timeout = 60 * 20;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    public function handle(MediaHasBeenAddedEvent $event): void
    {
        Pipeline::send($event->media)
            ->through([
                SetMediaStreams::class,
            ])
            ->thenReturn();
    }
}
