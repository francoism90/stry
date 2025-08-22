<?php

declare(strict_types=1);

namespace Domain\Playlists\Listeners;

use Domain\Groups\Actions\MarkAsViewed;
use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class PlaylistSessionListener implements ShouldQueueAfterCommit
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
    public $timeout = 60 * 3;

    /**
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * @var bool
     */
    public $failOnTimeout = true;

    public function handle(PlaylistHasBeenViewedEvent $event): void
    {
        $model = $event->playlist->getModel();

        if (! $model) {
            return;
        }

        app(MarkAsViewed::class)->handle($model, $event->user, $event->attributes);
    }
}
