<?php

declare(strict_types=1);

namespace App\Api\Groups\Controllers;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GroupToggleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Group $group, Video $video, Request $request): RedirectResponse|Response
    {
        Gate::authorize('view', $video);

        // Get the currently authenticated user
        $user = $request->user();

        // Toggle the group association based on the type
        $group = match ($group->type) {
            GroupType::Liked => $user->toggleLiked($video),
            GroupType::Saved => $user->toggleSaved($video),
            default => abort(422, 'Invalid group type provided.'),
        };

        $result = $group->hasGroupable($video)
            ? __('Added to :group.', ['group' => $group->title])
            : __('Removed from :group.', ['group' => $group->title]);

        if ($request->inertia()) {
            // Notify the user
            Inertia::flash([
                'title' => (string) $video->name,
                'description' => $result,
            ]);

            return back();
        }

        return response()->noContent();
    }
}
