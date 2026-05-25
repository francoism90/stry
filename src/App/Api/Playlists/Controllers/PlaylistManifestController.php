<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Exceptions\PlaylistTypeException;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Foxws\Shaka\Facades\Shaka;
use Foxws\Streamer\Facades\Streamer;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Spatie\ResponseCache\Attributes\Cache;

class PlaylistManifestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('throttle:vod'),
            new Middleware('signed'),
        ];
    }

    #[Cache(lifetime: 10 * 60, tags: ['playlists'])]
    public function __invoke(Playlist $playlist, string $path): Responsable
    {
        Gate::authorize('view', $playlist);

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        // Choose the appropriate manifest handler based on the playlist type
        $manifestHandler = match ($playlist->getType()) {
            PlaylistType::Streamer => Streamer::dynamicDASHManifest(),
            PlaylistType::Packager => Shaka::dynamicDASHManifest(),
            default => throw PlaylistTypeException::invalidType($playlist->getType()),
        };

        return $manifestHandler
            ->fromDisk($playlist->getDisk())
            ->open($playlist->getPath($path))
            ->setInitUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path));
    }
}
