<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Videos\Jobs\RenderVideoRenditions;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\PlaybackSettings;

/**
 * Queues the renditions of the best clip when they differ from the ones the playback settings ask
 * for, as encoding them takes longer than processing the video.
 */
class QueueVideoRenditions
{
    public function __construct(
        private readonly PlaybackSettings $settings,
    ) {}

    public function handle(Video $video, Closure $next): mixed
    {
        $clip = $video->getClips()->first();

        if ($clip !== null && $clip->getRenditionHeights() !== $this->settings->renditions) {
            RenderVideoRenditions::dispatch($video);
        }

        return $next($video);
    }
}
