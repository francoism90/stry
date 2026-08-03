<?php

declare(strict_types=1);

namespace App\Web\Profiles\Controllers;

use Domain\Profiles\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class SwitchProfileController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Request $request, Profile $profile): RedirectResponse
    {
        Gate::authorize('view', $profile);

        $request->session()->put('profiles.current', $profile->getRouteKey());

        return back();
    }
}
