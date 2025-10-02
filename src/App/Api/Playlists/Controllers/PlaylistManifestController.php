<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Jobs\TrackPlaylist;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use ProtoneMedia\LaravelFFMpeg\Http\DynamicHLSPlaylist;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class PlaylistManifestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return Config::array('playlist.middleware', []);
    }

    public function __invoke(Playlist $playlist, string $path): DynamicHLSPlaylist
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        // Ensure the playlist is still valid
        abort_unless($playlist->isValid(), 404);

        // Mark the playlist as accessed
        TrackPlaylist::dispatchIf(
            ! $playlist->accessed_at?->lessThan(now()->subMinutes(10)),
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
