<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Foxws\Shaka\Facades\Shaka;
use Foxws\Shaka\Http\DynamicHLSPlaylist;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class PlaylistManifestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('signed'),
        ];
    }

    public function __invoke(Playlist $playlist, string $path): DynamicHLSPlaylist
    {
        Gate::authorize('view', $playlist);

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        return Shaka::dynamicHLSPlaylist()
            ->fromDisk($playlist->getDisk())
            ->open($playlist->getPath($path))
            ->setKeyUrlResolver(fn (string $path) => $playlist->getKeyUrlResolver($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setPlaylistUrlResolver(fn (string $path) => $playlist->getUrlResolver($path));
    }
}
