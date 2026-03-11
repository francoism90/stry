<?php

declare(strict_types=1);

namespace App\Api\Groups\Controllers;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
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
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(GroupType $type, Video $video, Request $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('view', $video);

        // Get the currently authenticated user
        $user = $request->user();

        // Toggle the group association based on the type
        $group = match ($type) {
            GroupType::Liked => $user->toggleLiked($video),
            GroupType::Saved => $user->toggleSaved($video),
            default => abort(422, 'Invalid group type provided.'),
        };

        // Return back with a success message
        $result = $group->hasGroupable($video)
            ? __('Added to :group.', ['group' => $type->label()])
            : __('Removed from :group.', ['group' => $type->label()]);

        return $request->inertia()
            ? Inertia::flash('message', $result)->back()
            : response()->json();
    }
}
