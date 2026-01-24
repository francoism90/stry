<?php

declare(strict_types=1);

namespace App\Client\Groups\Controllers;

use Domain\Groups\Enums\GroupType;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GroupClearController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(GroupType $type): RedirectResponse
    {
        abort_if(
            in_array($type,
                [GroupType::Custom, GroupType::Mixer]),
            422,
            'The group type is not supported for this action.'
        );

        // Find or create the group for the authenticated user
        $group = Auth::user()->findOrCreateGroup($type);

        // Authorize the user to update the group
        Gate::authorize('update', $group);

        // Detach all videos from the group
        defer(fn () => $group->videos()->detach());

        // Redirect back with a success message
        return Inertia::flash('message', 'Videos will be detached from the group shortly.')->back();
    }
}
