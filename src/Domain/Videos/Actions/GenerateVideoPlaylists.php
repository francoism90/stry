<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Playlists\Actions\CreateHlsPlaylist;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class GenerateVideoPlaylists
{
    public function handle(Video $video): mixed
    {
        return DB::transaction(function () use ($video) {
            $items = collect([
                'clips' => ['type' => 'clip'],
                'previews' => ['type' => 'preview', 'expires_at' => null],
            ]);

            $items
                ->reject(fn (array $attributes, string $key) => $video->hasPlaylist($attributes['type']) || ! $video->hasMedia($key))
                ->each(function (array $attributes, string $key) use ($video) {
                    // Get the first media item for the given key
                    $media = $video->getFirstMedia($key);

                    // Create a new playlist for the media
                    $playlist = $video->createPlaylist($attributes);

                    // Associate the media with the newly created playlist
                    app(CreateHlsPlaylist::class)->handle($playlist, $media->disk, $media->getPathRelativeToRoot());
                });
        });
    }
}
