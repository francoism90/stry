<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoSearchScope;
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
            'items' => Inertia::scroll(fn () => VideoResource::collection(Video::search($request->safe()->input('search'))
                ->tap(new VideoSearchScope(sort: $request->safe()->input('sort')))
                ->simplePaginate(24))),
        ]);
    }
}
