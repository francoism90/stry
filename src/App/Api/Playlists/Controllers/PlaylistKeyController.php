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
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlaylistKeyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('cache:public;max_age=604800;immutable'),
        ];
    }

    public function __invoke(Playlist $playlist, string $path): StreamedResponse
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        throw_if($playlist->isExpired(), ExpiredPlaylistException::make());

        // Sanitize the path to prevent directory traversal attacks
        $path = (new WhitespacePathNormalizer)->normalizePath($path);

        return $playlist->getSecretFilesystem()->response($playlist->getPath($path));
    }
}
