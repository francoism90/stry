<?php

declare(strict_types=1);

namespace Domain\Playlists\Listeners;

use Domain\Playlists\Actions\MarkPlaylistAsPublished;
use Domain\Playlists\Events\PlaylistHasBeenProcessedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Pipeline;

class ProcessPlaylist implements ShouldQueueAfterCommit
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
    public $timeout = 60 * 10;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    public function handle(PlaylistHasBeenProcessedEvent $event): void
    {
        Pipeline::send($event->playlist)
            ->through([
                MarkPlaylistAsPublished::class,
            ])
            ->thenReturn();
    }
}
