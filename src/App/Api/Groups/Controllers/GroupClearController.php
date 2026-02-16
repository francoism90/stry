<?php

declare(strict_types=1);

namespace App\Api\Groups\Controllers;

use Domain\Groups\Enums\GroupType;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GroupClearController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(GroupType $type, Request $request): RedirectResponse|JsonResponse
    {
        abort_if(
            in_array($type,
                [GroupType::Custom, GroupType::Mixer]),
            422,
            'The group type is not supported for this action.'
        );

        // Find or create the group for the authenticated user
        $group = $request->user()->findOrCreateGroup($type);

        // Authorize the user to update the group
        Gate::authorize('update', $group);

        // Detach all videos from the group
        defer(fn () => $group->videos()->detach());

        return $request->inertia()
            ? Inertia::flash('message', 'Videos will be detached from the group shortly.')->back()
            : response()->json();
    }
}
