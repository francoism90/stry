<?php

declare(strict_types=1);

namespace App\Client\Groups\Controllers;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
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

    public function __invoke(GroupType $type, Video $video): RedirectResponse
    {
        Gate::authorize('view', $video);

        // Get the currently authenticated user
        $user = Auth::user();

        // Toggle the group association based on the type
        $group = match ($type) {
            GroupType::Liked => $user->toggleLiked($video),
            GroupType::Saved => $user->toggleSaved($video),
            default => abort(422, 'Invalid group type provided.'),
        };

        // Return back with a success message
        $result = $group->hasGroupable($video)
            ? __('Video added to :group group.', ['group' => $type->label()])
            : __('Video removed from :group group.', ['group' => $type->label()]);

        return Inertia::flash('message', $result)->back();
    }
}
