<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Videos\Jobs\WarmVideoStream;
use Domain\Videos\Models\Video;

/**
 * Queues indexing the stream of videos with a clip, so the first play doesn't wait for ffprobe to read the whole file.
 */
class QueueVideoStream
{
    public function handle(Video $video, Closure $next): mixed
    {
        if ($video->hasMedia('clips')) {
            WarmVideoStream::dispatch($video);
        }

        return $next($video);
    }
}
