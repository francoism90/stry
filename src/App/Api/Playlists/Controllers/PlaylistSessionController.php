<?php

declare(strict_types=1);

namespace App\Api\Playlists\Controllers;

use App\Api\Playlists\Requests\PlaylistViewRequest;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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

    public function __invoke(Playlist $playlist, PlaylistViewRequest $request): Response|RedirectResponse
    {
        // Authorize the user to view the playlist
        Gate::authorize('view', [$model = $playlist->getModel(), $playlist]);

        // Ensure the playlist is not expired
        abort_if($playlist->isExpired(), 410);

        // Dispatch the viewed event
        VideoHasBeenViewedEvent::dispatchIf($model instanceof Video,
            $model,
            $request->user(),
            $request->safe()->only(['time']),
        );

        return response()->noContent();
    }
}
