<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Playlists\Actions\CreateNewPlaylist;
use Domain\Playlists\Jobs\MakePlaylistable;
use Domain\Videos\Exceptions\InvalidVideoException;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoPlaylist
{
    public function handle(Video $video, array $attributes = [], bool $force = false): mixed
    {
        return DB::transaction(function () use ($video, $attributes, $force) {
            // If the video already has a playlist or has pending playlists, skip creation
            if (($video->currentPlaylist() || $video->hasPendingPlaylists()) && ! $force) {
                return;
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            throw_unless($media, InvalidVideoException::emptyClipCollection($video));

            // Create a new playlist for the video
            $playlist = app(CreateNewPlaylist::class)->handle($video, $attributes);

            // Create a new playlist for the video
            MakePlaylistable::dispatch($playlist, $media->disk, $media->getPathRelativeToRoot());
        });
    }
}
