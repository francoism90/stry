<?php

declare(strict_types=1);

namespace Modules\Api\Videos\Controllers;

use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Api\Videos\Requests\VideoViewRequest;

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
