<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlaylistEncryptionKeyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('signed'),
        ];
    }

    public function __invoke(Playlist $playlist, string $path): StreamedResponse
    {
        Gate::authorize('view', $playlist);

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        // Check if playlist has encryption enabled
        if (! $playlist->encryption_key) {
            abort(404);
        }

        // Get the encryption key file from storage
        $keyPath = $playlist->getPath($path);

        if (! Storage::disk($playlist->getDisk())->exists($keyPath)) {
            abort(404);
        }

        // Stream the key directly from storage (though 16 bytes is tiny, this is cleaner)
        return Storage::disk($playlist->getDisk())->response($keyPath, null, [
            'Content-Type' => 'application/octet-stream',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
