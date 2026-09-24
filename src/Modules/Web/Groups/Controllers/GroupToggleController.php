<?php

declare(strict_types=1);

namespace Modules\Web\Groups\Controllers;

use Domain\Groups\Models\Group;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class GroupToggleController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
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

        toast(title: (string) $video->name, description: $result);

        return back();
    }
}
