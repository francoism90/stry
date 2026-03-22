<?php

declare(strict_types=1);

namespace App\Api\Videos\Controllers;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoSaveController extends Controller implements HasMiddleware
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

        $group = $request->user()->toggleInGroup($video, GroupType::Saved);

        Inertia::flash([
            'title' => (string) $video->name,
            'description' => $group->hasGroupable($video)
                ? __('Added to :group.', ['group' => $group->title])
                : __('Removed from :group.', ['group' => $group->title]),
            'type' => 'success',
        ]);

        return back();
    }
}
