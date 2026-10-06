<?php

declare(strict_types=1);

namespace Modules\Web\Account\Controllers;

use Domain\Users\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Api\Notifications\Resources\NotificationResource;

class NotificationsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function index(#[CurrentUser] User $user): Response
    {
        Gate::authorize('update', $user);

        $query = $user->notifications()->simplePaginate(perPage: 20);

        return Inertia::render('Account/NotificationIndex', [
            'notifications' => Inertia::scroll(fn () => NotificationResource::collection($query)),
        ]);
    }

    public function update(#[CurrentUser] User $user, string $notification): RedirectResponse
    {
        Gate::authorize('update', $user);

        $record = $user->notifications()->findOrFail($notification);

        if ($record->read_at) {
            $record->markAsUnread();
        } else {
            $record->markAsRead();
        }

        return back();
    }

    public function destroy(#[CurrentUser] User $user, string $notification): RedirectResponse
    {
        Gate::authorize('update', $user);

        $user->notifications()->findOrFail($notification)->delete();

        return back();
    }
}
