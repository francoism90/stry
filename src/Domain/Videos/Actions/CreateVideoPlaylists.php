<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Playlists\Actions\CreateNewPlaylist;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoPlaylists
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            // Create a preview playlist if it doesn't exist
            if (! $video->hasMedia('previews') && $media = $video->getFirstMedia('previews')) {
                app(CreateNewPlaylist::class)->handle($video, $media->disk, $media->getPathRelativeToRoot(), [
                    'type' => 'preview',
                    'expires_at' => null,
                ]);
            }

            // Create a video playlist if it doesn't exist
            if (! $video->hasMedia('clips') && $media = $video->getFirstMedia('clips')) {
                app(CreateNewPlaylist::class)->handle($video, $media->disk, $media->getPathRelativeToRoot(), [
                    'type' => 'clip',
                ]);
            }

            return $next($video);
        });
    }
}
