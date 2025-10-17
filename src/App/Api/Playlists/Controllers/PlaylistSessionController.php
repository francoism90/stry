<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use App\Api\Playlists\Requests\PlaylistViewRequest;
use Domain\Playlists\Actions\MarkPlaylistAsAccessed;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Actions\SyncVideoProgress;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class PlaylistSessionController extends Controller implements HasMiddleware
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

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        // Mark the playlist as accessed
        if (! $playlist->isRecentlyAccessed()) {
            app(MarkPlaylistAsAccessed::class)->handle($playlist);
        }

        // Track user playlist activity
        if ($playlist->getModel() instanceof Video) {
            app(SyncVideoProgress::class)->handle(
                video: $playlist->getModel(),
                user: $request->user(),
                attributes: $request->safe()->only('time')
            );
        }

        return response()->noContent();
    }
}
