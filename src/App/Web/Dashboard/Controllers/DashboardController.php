<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Web\Dashboard\Responses\VideoQueryCollection;
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
            'recommended' => Inertia::defer(fn () => new VideoQueryCollection(limit: 16), 'sections')->deepMerge()->matchOn('data.id'),
            'recent' => Inertia::defer(fn () => new VideoQueryCollection(type: 'newest', limit: 16), 'sections')->deepMerge()->matchOn('data.id'),
            'watching' => Inertia::defer(fn () => new VideoQueryCollection(type: 'watching', limit: 16), 'sections')->deepMerge()->matchOn('data.id'),
        ]);
    }
}
