<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;

class SearchVideosController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request, string $query = ''): Response
    {
        Gate::authorize('viewAny', Video::class);

        $sort = $request->input('sort');

        $scout = Video::search($query)
            ->tap(new VideoFilterScope(sort: $sort))
            ->simplePaginate(perPage: 24);

        return Inertia::render('App/Search/SearchVideos', [
            'search' => fn () => $query,
            'sort' => fn () => $sort,
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
        ]);
    }
}
