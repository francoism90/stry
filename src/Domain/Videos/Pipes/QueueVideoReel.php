<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Videos\Jobs\GenerateVideoReel;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\ProcessingSettings;

/**
 * Queues a reel for videos that don't have one yet. Encoding takes longer than processing may, so it runs as its own job.
 */
class QueueVideoReel
{
    public function __construct(
        private readonly ProcessingSettings $settings,
    ) {}

    public function handle(Video $video, Closure $next): mixed
    {
        if (! $this->settings->create_reels || ! $video->hasMedia('clips') || $video->hasReel()) {
            return $next($video);
        }

        GenerateVideoReel::dispatch($video);

        return $next($video);
    }
}
