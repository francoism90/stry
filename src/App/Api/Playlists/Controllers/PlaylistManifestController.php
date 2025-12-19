<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Foxws\Shaka\Facades\Shaka;
use Foxws\Shaka\Http\DynamicHLSPlaylist;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;

class PlaylistManifestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return Config::array('playlist.middleware', []);
    }

    public function __invoke(Playlist $playlist, string $path): DynamicHLSPlaylist
    {
        // Gate::authorize('view', [$playlist->getModel(), $playlist]);

        // Ensure the playlist is not expired
        // abort_if($playlist->isExpired(), 410);

        // Dispatch the viewed event
        // PlaylistHasBeenViewedEvent::dispatchIf(
        //     ! $playlist->isRecentlyAccessed(),
        //     $playlist,
        // );

        logger($playlist->getPath($path));

        return Shaka::dynamicHLSPlaylist()
            ->fromDisk($playlist->getDisk())
            ->open($playlist->getPath($path))
            ->setKeyUrlResolver(fn (string $path) => $playlist->getKeyUrlResolver($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setPlaylistUrlResolver(fn (string $path) => $playlist->getUrlResolver($path));
    }
}
