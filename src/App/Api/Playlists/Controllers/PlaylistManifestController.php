<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\Settings\PlaylistSettings;
use Foxws\Shaka\Facades\Shaka;
use Foxws\Shaka\Http\DynamicDASHManifest as ShakaDASHManifest;
use Foxws\Shaka\Http\DynamicHLSPlaylist as ShakaHLSPlaylist;
use Foxws\Streamer\Facades\Streamer;
use Foxws\Streamer\Http\DynamicDASHManifest as StreamerDASHManifest;
use Foxws\Streamer\Http\DynamicHLSPlaylist as StreamerHLSPlaylist;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

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
        $manifestHandler = str_ends_with($path, '.m3u8')
            ? $this->hlsPlaylist($playlist)
            : $this->dashManifest($playlist);

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

    protected function hlsPlaylist(Playlist $playlist): ShakaHLSPlaylist|StreamerHLSPlaylist
    {
        $playlistHandler = match ($playlist->getType()) {
            PlaylistType::Packager => Shaka::dynamicHLSPlaylist(),
            PlaylistType::Streamer => Streamer::dynamicHLSPlaylist(),
        };

        return $playlistHandler
            ->setKeyUrlResolver(fn (string $path) => $playlist->getKeyUrlResolver($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setPlaylistUrlResolver(fn (string $path) => $playlist->getUrlResolver($path));
    }

    protected function dashManifest(Playlist $playlist): ShakaDASHManifest|StreamerDASHManifest
    {
        $manifestHandler = match ($playlist->getType()) {
            PlaylistType::Packager => Shaka::dynamicDASHManifest(),
            PlaylistType::Streamer => Streamer::dynamicDASHManifest(),
        };

        return $manifestHandler
            ->setInitUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path))
            ->setMediaUrlResolver(fn (string $path) => $playlist->getMediaUrlResolver($path));
    }
}
