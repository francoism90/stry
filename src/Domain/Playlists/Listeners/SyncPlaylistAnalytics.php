<?php

declare(strict_types=1);

namespace Domain\Playlists\Listeners;

use Domain\Playlists\Actions\MarkPlaylistAsAccessed;
use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Pipeline;
use Spatie\RateLimitedMiddleware\RateLimited;

class SyncPlaylistAnalytics implements ShouldQueueAfterCommit
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
    public $timeout = 60 * 60;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    public function handle(PlaylistHasBeenViewedEvent $event): void
    {
        Pipeline::send($event->playlist)
            ->through([
                MarkPlaylistAsAccessed::class,
            ])
            ->thenReturn();
    }

    /**
     * @return array<int, object>
     */
    public function middleware(PlaylistHasBeenViewedEvent $event): array
    {
        return [
            (new RateLimited)->allow(30)->everySeconds(60)->releaseAfterOneMinute(),
            (new WithoutOverlapping($event->playlist->getKey()))->releaseAfter(30),
        ];
    }
}
