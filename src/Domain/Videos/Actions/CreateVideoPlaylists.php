<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Playlists\Actions\CreateNewPlaylist;
use Domain\Playlists\Jobs\ProcessPlaylist;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoPlaylists
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            $items = collect([
                'clips' => ['type' => 'clip'],
                'previews' => ['type' => 'preview', 'expires_at' => null],
            ]);

            $items
                ->filter(fn (array $attributes, string $key) => $video->hasMedia($key))
                ->each(function (array $attributes, string $key) use ($video) {
                    $media = $video->getFirstMedia($key);

                    // Create a new playlist for the media
                    $playlist = app(CreateNewPlaylist::class)->handle($video, $attributes);

                    // Associate the media with the newly created playlist
                    ProcessPlaylist::dispatch($playlist, $media->disk, $media->getPathRelativeToRoot());
                });

            return $next($video);
        });
    }
}
