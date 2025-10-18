<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Playlists\Actions\CreateHlsPlaylist;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class GenerateVideoClipPlaylist
{
    public function handle(Video $video, Closure $next): Video
    {
        return DB::transaction(function () use ($video, $next) {
            // If the video already has playlists or has no clips, skip processing
            if ($video->hasPlaylist('clip') || ! $video->hasMedia('clips')) {
                return $next($video);
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Create a new playlist for the media
            $playlist = $video->createPlaylist([
                'type' => 'clip',
            ]);

            // Generate the HLS playlist for the media
            app(CreateHlsPlaylist::class)->handle($playlist, $media->disk, $media->getPathRelativeToRoot());

            return $next($video);
        });
    }
}
