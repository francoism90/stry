<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Actions\CreateVideoPreview;
use Domain\Videos\Events\VideoHasBeenAddedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class GenerateVideoPreview implements ShouldQueueAfterCommit
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
    public $backoff = 3;

    /**
     * @var int
     */
    public $timeout = 60 * 60;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    public function handle(VideoHasBeenAddedEvent $event): void
    {
        app(CreateVideoPreview::class)->handle($event->video);
    }
}
