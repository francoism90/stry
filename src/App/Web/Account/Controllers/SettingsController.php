<?php

declare(strict_types=1);

namespace App\Web\Account\Controllers;

use App\Web\Users\Responses\UserResourceProperty;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Request $request): Response
    {
        Gate::authorize('update', $request->user());

        return Inertia::render('Account/AccountSettings', [
            'user' => fn () => new UserResourceProperty(
                $request->user(),
                ['name', 'email', 'avatar', 'settings'],
            ),
        ]);
    }
}
