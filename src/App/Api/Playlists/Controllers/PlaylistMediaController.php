<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Jobs\UpdateActivity;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;
use League\Flysystem\WhitespacePathNormalizer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlaylistMediaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return config('playlist.middleware', []);
    }

    public function __invoke(Playlist $playlist, string $path): StreamedResponse
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        // Sanitize the path to prevent directory traversal attacks
        $path = (new WhitespacePathNormalizer)->normalizePath($path);

        // Update the playlist activity
        UpdateActivity::dispatch($playlist);

        return $playlist->getFilesystem()->response($playlist->getPath($path));
    }
}
