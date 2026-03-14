<?php

declare(strict_types=1);

namespace App\Api\Notifications\Controllers;

use Foundation\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MarkAllNotificationsReadController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $request->user());

        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->inertia()) {
            Inertia::flash([
                'title' => 'All caught up',
                'description' => 'All notifications have been marked as read.',
                'icon' => 'i-lucide-check-check',
            ]);

            return back();
        }

        return response()->json();
    }
}
