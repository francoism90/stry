<?php

declare(strict_types=1);

namespace App\Modules\Account\Controllers;

use App\Modules\Notifications\Resources\NotificationResource;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class NotificationsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function index(Request $request): Response
    {
        Gate::authorize('update', $request->user());

        return Inertia::render('App/Account/NotificationIndex', [
            'notifications' => Inertia::scroll(fn () => NotificationResource::collection(
                $request->user()->notifications()->simplePaginate(perPage: 20)
            )),
        ]);
    }

    public function update(Request $request, string $notification): RedirectResponse
    {
        Gate::authorize('update', $request->user());

        $record = $request->user()->notifications()->findOrFail($notification);

        if ($record->read_at) {
            $record->markAsUnread();
        } else {
            $record->markAsRead();
        }

        return back();
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        Gate::authorize('update', $request->user());

        $request->user()->notifications()->findOrFail($notification)->delete();

        return back();
    }
}
