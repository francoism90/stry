<?php

declare(strict_types=1);

namespace App\Api\Groups\Controllers;

use Domain\Groups\Models\Group;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GroupToggleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(Group $group, Video $video, Request $request): RedirectResponse
    {
        Gate::authorize('view', $video);
        Gate::authorize('update', $group);

        // Toggle the group association
        $video->toggleGroup($group);

        $result = $group->hasGroupable($video)
            ? __('Added to :group.', ['group' => $group->title])
            : __('Removed from :group.', ['group' => $group->title]);

        Inertia::flash([
            'title' => (string) $video->name,
            'description' => $result,
            'type' => 'success',
        ]);

        return back();
    }
}
