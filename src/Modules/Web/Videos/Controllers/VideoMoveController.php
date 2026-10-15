<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Controllers;

use Domain\Users\Models\User;
use Domain\Videos\Jobs\MoveVideo;
use Domain\Videos\Models\Video;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Modules\Api\Videos\Requests\VideoMoveRequest;

class VideoMoveController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('role:super-admin'),
        ];
    }

    public function __invoke(VideoMoveRequest $request, Video $video, #[CurrentUser] User $user): RedirectResponse
    {
        Gate::authorize('update', $video);

        MoveVideo::dispatch($video, $user, $request->string('disk')->value());

        toast(title: (string) $video->name, description: __('Queued for move.'), type: 'info');

        return back();
    }
}
