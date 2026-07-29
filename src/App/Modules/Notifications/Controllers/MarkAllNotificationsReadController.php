<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MarkAllNotificationsReadController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request): RedirectResponse
    {
        Gate::authorize('update', $request->user());

        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        Inertia::flash([
            'title' => 'All caught up',
            'description' => 'All notifications have been marked as read.',
            'type' => 'success',
        ]);

        return back();
    }
}
