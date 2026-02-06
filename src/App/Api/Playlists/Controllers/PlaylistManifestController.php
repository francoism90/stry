<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Foxws\Shaka\Facades\Shaka;
use Foxws\Streamer\Facades\Streamer;
use Illuminate\Contracts\Support\Responsable;
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

    public function __invoke(Playlist $playlist, string $path): Responsable
    {
        Gate::authorize('view', $playlist);

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        // Choose the appropriate manifest handler based on the playlist type
        $manifestHandler = match ($playlist->type) {
            PlaylistType::Streamer => Streamer::dynamicDASHManifest(),
            default => Shaka::dynamicDASHManifest(),
        };

        return $manifestHandler
            ->fromDisk($playlist->getDisk())
            ->open($playlist->getPath($path))
            ->setInitUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path));
    }
}
