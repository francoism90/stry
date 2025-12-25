<?php

declare(strict_types=1);

namespace Domain\Media\Listeners;

use Domain\Media\Actions\SetMediaStreams;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
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
    public $tries = 3;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var int
     */
    public $timeout = 60 * 30;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public function handle(MediaHasBeenAddedEvent $event): void
    {
        Pipeline::send($event->media)
            ->through([
                SetMediaStreams::class,
            ])
            ->thenReturn();
    }

    /**
     * @return array<int, object>
     */
    public function middleware(MediaHasBeenAddedEvent $event): array
    {
        return [
            (new WithoutOverlapping($event->media->getKey()))->releaseAfter(10),
        ];
    }
}
