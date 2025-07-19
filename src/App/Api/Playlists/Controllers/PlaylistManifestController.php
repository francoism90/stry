<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Exceptions\ExpiredPlaylistException;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use League\Flysystem\WhitespacePathNormalizer;
use ProtoneMedia\LaravelFFMpeg\Http\DynamicHLSPlaylist;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class PlaylistManifestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('cache:public;max_age=604800;immutable'),
        ];
    }

    public function __invoke(Playlist $playlist, string $path): DynamicHLSPlaylist
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        throw_if($playlist->isExpired(), ExpiredPlaylistException::make());

        // Sanitize the path to prevent directory traversal attacks
        $path = (new WhitespacePathNormalizer)->normalizePath($path);

        return FFMpeg::dynamicHLSPlaylist()
            ->fromDisk($playlist->getDisk())
            ->open("{$playlist->getPath()}/{$path}")
            ->setKeyUrlResolver(fn (string $path) => route('api.playlists.key', ['playlist' => $playlist, 'path' => $path]))
            ->setMediaUrlResolver(fn (string $path) => route('api.playlists.media', ['playlist' => $playlist, 'path' => $path]))
            ->setPlaylistUrlResolver(fn (string $path) => route('api.playlists.playlist', ['playlist' => $playlist, 'path' => $path]));
    }
}
