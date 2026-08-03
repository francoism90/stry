<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Exceptions\PlaylistTypeException;
use Domain\Playlists\Models\Playlist;
use Foxws\Shaka\Facades\Shaka;
use Foxws\Streamer\Facades\Streamer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class PlaylistManifestController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('throttle:vod'),
            new Middleware('signed'),
            new Middleware('cache.bypass'),
        ];
    }

    public function __invoke(Request $request, Playlist $playlist, string $path): Response
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

        // Get the manifest cache lifetime
        $manifestCacheLifetime = Playlist::getManifestCacheLifetime();

        // Generate the manifest response
        $response = $manifestHandler
            ->fromDisk($playlist->getDisk())
            ->open($playlist->getPath($path))
            ->setInitUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->toResponse($request);

        // Set appropriate cache headers
        $response->headers->set('Cache-Control', "public, max-age={$manifestCacheLifetime}, stale-while-revalidate=30");

        return $response;
    }
}
