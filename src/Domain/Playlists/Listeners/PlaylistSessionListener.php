<?php

declare(strict_types=1);

namespace Domain\Playlists\Listeners;

use Domain\Groups\Actions\MarkAsViewed;
use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;

class PlaylistSessionListener implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @var int
     */
    public $timeout = 60 * 5;

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

        if (! $model instanceof Model) {
            return;
        }

        app(MarkAsViewed::class)->handle($model, $event->user, $event->attributes);
    }
}
