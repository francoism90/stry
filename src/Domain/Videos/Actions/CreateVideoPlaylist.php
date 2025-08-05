<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Playlists\Actions\CreateNewPlaylist;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Jobs\ProcessPlaylist;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoPlaylist
{
    public function handle(Video $video, PlaylistType $type, bool $force = false): mixed
    {
        return DB::transaction(function () use ($video, $type, $force) {
            if ($video->hasPlaylists($type) && ! $force) {
                return;
            }

            // Check if the video has media for the specified type
            $media = match ($type) {
                PlaylistType::Preview => $video->getFirstMedia('previews'),
                PlaylistType::Clip => $video->getFirstMedia('clips'),
                default => $video->getFirstMedia($type->value),
            };

            // Create a new playlist of the video clip
            $attributes = ['type' => $type];

            // We don't want to set an expiration date for preview playlists
            if ($type === PlaylistType::Preview) {
                $attributes['expires_at'] = null;
            }

            $playlist = app(CreateNewPlaylist::class)->handle($video, $attributes);

            // Add the media to the playlist
            ProcessPlaylist::dispatch($playlist, $media->disk, $media->getPathRelativeToRoot());
        });
    }
}
