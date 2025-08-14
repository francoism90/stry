<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use App\Api\Playlists\Requests\PlaylistViewRequest;
use Domain\Playlists\Events\PlaylistHasBeenViewedEvent;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;
use ProtoneMedia\LaravelFFMpeg\Http\DynamicHLSPlaylist;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class PlaylistSessionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return config('playlist.middleware', []);
    }

    public function __invoke(Playlist $playlist, PlaylistViewRequest $request)
    {
        Gate::authorize('view', [$playlist->getModel(), $playlist]);

        logger($request->input('time'));

        // PlaylistHasBeenViewedEvent::dispatchIf(
        //     ! $playlist->accessed_at->lessThan(now()->subMinutes(10)),
        //     $playlist
        // );
    }
}
