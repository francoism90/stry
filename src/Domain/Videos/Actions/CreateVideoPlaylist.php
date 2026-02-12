<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;

class CreateVideoPlaylist
{
    public function handle(Video $video, Closure $next): array
    {
        // Check if we should create a playlist for this video
        if (! Video::shouldCreatePlaylist() || $video->hasPlaylist()) {
            return $next($video);
        }

        // Dispatch the job to create the playlist
        PlaylistVideo::dispatch($video);

        return $next($video);
    }
}
