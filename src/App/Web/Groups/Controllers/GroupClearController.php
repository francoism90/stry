<?php

declare(strict_types=1);

namespace App\Web\Groups\Controllers;

use Domain\Groups\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GroupClearController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Group $group): RedirectResponse
    {
        Gate::authorize('update', $group);

        defer(fn () => $group->videos()->detach());

        Inertia::flash([
            'title' => $group->title,
            'description' => __('All videos will be detached shortly.'),
            'type' => 'info',
        ]);

        return back();
    }
}
