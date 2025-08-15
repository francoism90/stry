<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Actions\GetVideoSuggestions;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
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

        $builder = Video::query()
            ->verified()
            ->take(12);

        return Inertia::render('Dashboard/DashboardIndex', [
            'recommended' => Inertia::defer(fn () => $builder->clone()->inRandomOrder()->get()->toResourceCollection(VideoResource::class), 'sections')->deepMerge()->matchOn('data.id'),
            'recent' => Inertia::defer(fn () => $builder->clone()->latest()->get()->toResourceCollection(VideoResource::class), 'sections')->deepMerge()->matchOn('data.id'),
            'watching' => Inertia::defer(fn () => $builder->clone()->watching()->get()->toResourceCollection(VideoResource::class), 'sections')->deepMerge()->matchOn('data.id'),
        ]);
    }
}
