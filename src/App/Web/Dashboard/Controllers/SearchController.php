<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Web\Dashboard\Responses\VideoScoutCollection;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        return Inertia::render('Dashboard/SearchIndex', [
            'search' => fn () => $request->safe()->input('search'),
            'sort' => fn () => $request->safe()->input('sort'),
            'items' => Inertia::defer(fn () => new VideoScoutCollection(
                query: $request->safe()->input('search'),
                sort: $request->safe()->input('sort'),
                page: (int) $request->safe()->input('page', 1),
            ))->deepMerge()->matchOn('data.id'),
        ]);
    }
}
