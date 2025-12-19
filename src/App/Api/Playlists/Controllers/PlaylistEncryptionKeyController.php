<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class PlaylistEncryptionKeyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return Config::array('playlist.middleware', []);
    }

    public function __invoke(Playlist $playlist, string $path): Response
    {
        // Check if playlist has encryption enabled
        if (! $playlist->encryption_key) {
            abort(404);
        }

        // Get the encryption key file from storage
        $keyPath = $playlist->getPath($path);

        $keyContent = Storage::disk($playlist->getRotationKeyDisk())->get($keyPath);

        // Return the key as binary data
        return response($keyContent, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="encryption.key"',
            // 'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
