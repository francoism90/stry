<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use App\Api\Playlists\Requests\PlaylistViewRequest;
use Domain\Playlists\Jobs\RecordPlaylist;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class PlaylistProgressController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
            new Middleware('subscribed'),
        ];
    }

    public function __invoke(Playlist $playlist, PlaylistViewRequest $request): Response
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        // Only dispatch if we have progress data to sync
        RecordPlaylist::dispatchIf($request->safe()->filled('time'),
            $playlist, $request->user(), $request->safe()->all()
        );

        return response()->noContent();
    }
}
