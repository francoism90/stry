<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlaylistKeyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return Config::array('playlist.middleware', []);
    }

    public function __invoke(Playlist $playlist, string $path): StreamedResponse
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        $path = $playlist->getPath($path);

        return $playlist->getSecretFilesystem()->response($path);
    }
}
