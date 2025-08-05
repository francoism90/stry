<?php

declare(strict_types=1);

namespace Domain\Videos\Listeners;

use Domain\Playlists\Actions\CreateHlsPlaylist;
use Domain\Playlists\Actions\CreateNewPlaylist;
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

        // Get the first media item for the video
        $media = $event->video->getFirstMedia('previews');

        // Create a new playlist for the video
        $playlist = app(CreateNewPlaylist::class)->handle($event->video, [
            'type' => 'previews',
            'expires_at' => null,
        ]);

        // Create an HLS playlist for the video
        app(CreateHlsPlaylist::class)->handle($playlist, $media->disk, $media->getPathRelativeToRoot());
    }
}
