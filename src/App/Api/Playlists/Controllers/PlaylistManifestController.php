<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Foxws\Streamer\Facades\Streamer;
use Foxws\Streamer\Http\DynamicDASHManifest;
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

    public function __invoke(Playlist $playlist, string $path): DynamicDASHManifest
    {
        Gate::authorize('view', $playlist);

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        return Streamer::dynamicDASHManifest()
            ->fromDisk($playlist->getDisk())
            ->open($playlist->getPath($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setInitUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path));
    }
}
