<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Actions\GenerateVideoClipPlaylist;
use Domain\Videos\Actions\GenerateVideoPreviewPlaylist;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Pipeline;

class CreateVideoPlaylists implements ShouldQueueAfterCommit
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
    public $timeout = 60 * 60 * 8;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public function handle(VideoHasBeenViewedEvent $event): void
    {
        Pipeline::send($event->video)
            ->through([
                GenerateVideoClipPlaylist::class,
                GenerateVideoPreviewPlaylist::class,
            ])
            ->thenReturn();
    }

    /**
     * @return array<int, object>
     */
    public function middleware(VideoHasBeenViewedEvent $event): array
    {
        return [
            (new WithoutOverlapping($event->video->getKey()))->dontRelease(),
        ];
    }
}
