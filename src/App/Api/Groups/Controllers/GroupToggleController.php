<?php

declare(strict_types=1);

namespace App\Api\Groups\Controllers;

use Domain\Groups\Enums\GroupType;
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

        // Get the currently authenticated user
        $user = $request->user();

        // Toggle the group association based on the type
        $group = match ($group->type) {
            GroupType::Liked => $user->toggleLiked($video),
            GroupType::Saved => $user->toggleSaved($video),
            GroupType::Custom => $video->toggleGroup($group),
            default => abort(422, 'Invalid group type provided.'),
        };

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
