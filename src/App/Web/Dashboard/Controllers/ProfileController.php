<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(): Response
    {
        Gate::authorize('update', Auth::user());

        return Inertia::render('Dashboard/ProfileIndex');
    }
}
