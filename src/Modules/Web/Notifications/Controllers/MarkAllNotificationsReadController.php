<?php

declare(strict_types=1);

namespace Modules\Web\Notifications\Controllers;

use Domain\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class MarkAllNotificationsReadController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(#[CurrentUser] User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $user->unreadNotifications()->update(['read_at' => now()]);

        toast(title: 'All caught up', description: 'All notifications have been marked as read.');

        return back();
    }
}
