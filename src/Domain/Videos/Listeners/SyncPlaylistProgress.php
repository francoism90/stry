<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Videos\Actions\SetVideoProgress;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Spatie\RateLimitedMiddleware\RateLimited;

class SyncPlaylistProgress implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * @var string|null
     */
    public $queue = 'broadcasts';

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

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public function handle(VideoHasBeenViewedEvent $event): void
    {
        app(SetVideoProgress::class)->handle(
            video: $event->video,
            user: $event->user,
            attributes: $event->attributes
        );
    }

    /**
     * @return array<int, object>
     */
    public function middleware(VideoHasBeenViewedEvent $event): array
    {
        return [
            (new RateLimited)->allow(30)->everySeconds(60)->dontRelease(),
            (new WithoutOverlapping($event->video->getKey()))->dontRelease(),
        ];
    }
}
