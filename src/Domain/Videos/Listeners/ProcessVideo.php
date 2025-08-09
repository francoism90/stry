<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Actions\CreateVideoCaptions;
use Domain\Videos\Actions\CreateVideoPreview;
use Domain\Videos\Actions\CreateVideoThumbnail;
use Domain\Videos\Actions\GenerateVideoPlaylists;
use Domain\Videos\Actions\MarkVideoAsPublished;
use Domain\Videos\Events\VideoHasBeenAddedEvent;
use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Pipeline;

class ProcessVideo implements ShouldQueueAfterCommit
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
    public $timeout = 60 * 60 * 4;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    public function handle(VideoHasBeenAddedEvent|VideoHasBeenUpdatedEvent|VideoHasBeenViewedEvent $event): void
    {
        Pipeline::send($event->video)
            ->through([
                CreateVideoCaptions::class,
                CreateVideoThumbnail::class,
                CreateVideoPreview::class,
                MarkVideoAsPublished::class,
                GenerateVideoPlaylists::class,
            ])
            ->thenReturn();
    }
}
