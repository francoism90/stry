<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use Domain\Videos\Actions\GetVideoSuggestions;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(): Response
    {
        Gate::authorize('viewAny', Video::class);

        return Inertia::render('Dashboard/DashboardIndex', [
            'recent' => Inertia::defer(fn () => app(GetVideoSuggestions::class)->handle(), 'sections'),
        ]);
    }
}
