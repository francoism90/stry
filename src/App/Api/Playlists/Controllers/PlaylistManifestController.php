<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Exceptions\PlaylistTypeException;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\Settings\PlaylistSettings;
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

    public function __invoke(Request $request, Playlist $playlist, string $path, PlaylistSettings $settings): Response
    {
        Gate::authorize('view', $playlist);

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        // Both playlist engines package DASH and HLS from the same CMAF streams
        $isHlsRequest = str_ends_with($path, '.m3u8');

        // Choose the appropriate manifest handler based on the requested format and playlist type
        $manifestHandler = match (true) {
            $isHlsRequest && $playlist->getType() === PlaylistType::Streamer => Streamer::dynamicHLSPlaylist()
                ->setKeyUrlResolver(fn (string $path) => $playlist->getKeyUrlResolver($path))
                ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
                ->setPlaylistUrlResolver(fn (string $path) => $playlist->getUrlResolver($path)),
            $isHlsRequest && $playlist->getType() === PlaylistType::Packager => Shaka::dynamicHLSPlaylist()
                ->setKeyUrlResolver(fn (string $path) => $playlist->getKeyUrlResolver($path))
                ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
                ->setPlaylistUrlResolver(fn (string $path) => $playlist->getUrlResolver($path)),
            $playlist->getType() === PlaylistType::Streamer => Streamer::dynamicDASHManifest()
                ->setInitUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
                ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path)),
            $playlist->getType() === PlaylistType::Packager => Shaka::dynamicDASHManifest()
                ->setInitUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
                ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path)),
            default => throw PlaylistTypeException::invalidType($playlist->getType()),
        };

        // Get the manifest cache lifetime
        $manifestCacheLifetime = $settings->manifest_cache_lifetime;

        // Generate the manifest response
        $response = $manifestHandler
            ->fromDisk($playlist->getDisk())
            ->open($playlist->getPath($path))
            ->toResponse($request);

        // Set appropriate cache headers
        $response->headers->set('Cache-Control', "public, max-age={$manifestCacheLifetime}, stale-while-revalidate=30");

        return $response;
    }
}
