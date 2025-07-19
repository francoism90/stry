<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Playlists\Actions\CreateHlsPlaylist;
use Domain\Playlists\Actions\CreateNewPlaylist;
use Domain\Videos\Events\VideoHasBeenTranscoded;
use Domain\Videos\Exceptions\EmptyClipCollection;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoPlaylist
{
    public function handle(Video $video, array $attributes = [], bool $force = false): mixed
    {
        return DB::transaction(function () use ($video, $attributes, $force) {
            // If the video already has a playlist, and we're not forcing recreation, return early
            if ($video->currentPlaylist() && ! $force) {
                return;
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            throw_unless($media, EmptyClipCollection::make());

            // Create a new playlist for the video
            $playlist = app(CreateNewPlaylist::class)->handle($video, $attributes);

            // Create a new playlist for the video
            app(CreateHlsPlaylist::class)->handle($playlist, $media->disk, $media->getPathRelativeToRoot());

            // Dispatch the event that the video has been transcoded
            VideoHasBeenTranscoded::dispatch($video);
        });
    }
}
