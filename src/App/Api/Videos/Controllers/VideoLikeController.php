<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoLikeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(Video $video, Request $request): RedirectResponse
    {
        Gate::authorize('view', $video);

        $group = $request->user()->toggleLiked($video);

        Inertia::flash([
            'title' => (string) $video->name,
            'description' => $group->hasGroupable($video)
                ? __('Added to :group.', ['group' => $group->title])
                : __('Removed from :group.', ['group' => $group->title]),
        ]);

        return back();
    }
}
