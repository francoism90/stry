<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use App\Api\Playlists\Requests\PlaylistViewRequest;
use Domain\Playlists\Jobs\RecordPlaylist;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PlaylistProgressController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Playlist $playlist, PlaylistViewRequest $request)
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        // Only dispatch if we have progress data to sync
        RecordPlaylist::dispatchIf($request->safe()->filled('progress'),
            $playlist, Auth::user(), $request->safe()->all()
        );
    }
}
