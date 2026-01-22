<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class PlaylistLicenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function __invoke(Playlist $playlist): JsonResponse
    {
        Gate::authorize('view', $playlist);

        // Get encryption key and key ID from playlist
        $keyId = base64_encode($playlist->encryption_key_id ?? random_bytes(16));
        $key = base64_encode($playlist->encryption_key ?? random_bytes(16));

        // Clear Key PSSH format (JSON Web Key format)
        return response()->json([
            'keys' => [
                [
                    'kty' => 'oct', // Key type: octet sequence (raw bytes)
                    'kid' => $keyId, // Key ID (base64url encoded)
                    'k' => $key, // Key value (base64url encoded)
                ],
            ],
            'type' => 'temporary', // License type
        ]);
    }
}
