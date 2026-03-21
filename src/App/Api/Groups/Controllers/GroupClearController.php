<?php

declare(strict_types=1);

namespace App\Api\Groups\Controllers;

use Domain\Groups\Models\Group;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    public function __invoke(Group $group, Request $request): RedirectResponse|Response
    {
        // Authorize the user to update the group
        Gate::authorize('update', $group);

        // Detach all videos from the group
        defer(fn () => $group->videos()->detach());

        if ($request->inertia()) {
            // Notify the user
            Inertia::flash([
                'title' => $group->title,
                'description' => __('All videos will be detached shortly.'),
            ]);

            return back();
        }

        return response()->noContent();
    }
}
