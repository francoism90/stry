<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Jobs\AccessedPlaylist;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use ProtoneMedia\LaravelFFMpeg\Http\DynamicHLSPlaylist;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class PlaylistManifestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return config('playlist.middleware', []);
    }

    public function __invoke(Playlist $playlist, string $path): DynamicHLSPlaylist
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        // Mark the playlist as accessed
        AccessedPlaylist::dispatchIf(
            ! $playlist->accessed_at->lessThan(now()->subMinutes(10)),
            $playlist
        );

        return FFMpeg::dynamicHLSPlaylist()
            ->fromDisk($playlist->getDisk())
            ->open($playlist->getPath($path))
            ->setKeyUrlResolver(fn (string $path) => $playlist->getKeyUrlResolver($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setPlaylistUrlResolver(fn (string $path) => $playlist->getUrlResolver($path));
    }
}
