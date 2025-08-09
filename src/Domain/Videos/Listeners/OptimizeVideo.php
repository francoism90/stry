<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Actions\GenerateVideoPlaylists;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Pipeline;

class OptimizeVideo implements ShouldQueueAfterCommit
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

    public function handle(VideoHasBeenViewedEvent $event): void
    {
        Pipeline::send($event->video)
            ->through([
                GenerateVideoPlaylists::class,
            ])
            ->thenReturn();
    }
}
