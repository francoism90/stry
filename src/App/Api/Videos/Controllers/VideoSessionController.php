<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use App\Api\Videos\Requests\VideoViewRequest;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class VideoSessionController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('throttle:vod'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Video $video, VideoViewRequest $request): Response|RedirectResponse
    {
        // Authorize the user to view the video
        Gate::authorize('view', $video);

        // Dispatch the viewed event
        VideoHasBeenViewedEvent::dispatch(
            $video,
            $request->user(),
            $request->safe()->only(['time']),
        );

        return response()->noContent();
    }
}
