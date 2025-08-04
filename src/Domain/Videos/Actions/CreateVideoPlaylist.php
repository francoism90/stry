<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Playlists\Actions\CreateNewPlaylist;
use Domain\Playlists\Jobs\TranscodePlaylist;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoPlaylist
{
    public function handle(Video $video, bool $force = false): mixed
    {
        return DB::transaction(function () use ($video, $force) {
            if (! $video->hasMedia('clips') || ($video->hasPlaylists('clips') && ! $force)) {
                return;
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Create a new playlist for the video
            $playlist = app(CreateNewPlaylist::class)->handle($video, ['type' => 'clips']);

            // Create a new playlist for the video
            TranscodePlaylist::dispatch($playlist, $media->disk, $media->getPathRelativeToRoot());
        });
    }
}
