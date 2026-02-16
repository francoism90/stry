<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Actions\ExtractVideoCaptions;
use Domain\Videos\Actions\MarkVideoAsVerified;
use Domain\Videos\Actions\ProcessVideoPlaylist;
use Domain\Videos\Events\VideoHasBeenAddedEvent;
use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Pipeline;
use Spatie\RateLimitedMiddleware\RateLimited;

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

    public function handle(VideoHasBeenAddedEvent|VideoHasBeenUpdatedEvent $event): void
    {
        // Send the video through the pipeline of actions
        Pipeline::send($event->video)
            ->through([
                ExtractVideoCaptions::class,
                MarkVideoAsVerified::class,
                ProcessVideoPlaylist::class,
            ])
            ->thenReturn();
    }

    /**
     * @return array<int, object>
     */
    public function middleware(VideoHasBeenAddedEvent|VideoHasBeenUpdatedEvent $event): array
    {
        return [
            (new RateLimited)->allow(30)->everySeconds(60)->releaseAfterRandomSeconds(10),
            (new WithoutOverlapping($event->video->getKey()))->releaseAfter(10),
        ];
    }
}
