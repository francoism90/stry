<?php

declare(strict_types=1);

namespace App\Api\Tags\Controllers;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Foundation\Http\Controllers\Controller;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Spatie\ResponseCache\Attributes\Cache;
use Support\Scout\Sorts\VideosSorter;

class TagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('precognitive'),
        ];
    }

    #[Cache(lifetime: 10 * 60, tags: ['tags'])]
    public function index(Request $request): Paginator
    {
        Gate::authorize('viewAny', Tag::class);

        return ScoutBuilder::for(Tag::search($request->input('search', '')))
            ->query(fn (TagQueryBuilder $query) => $query->withCount('videos'))
            ->allowedFilters(
                AllowedFilter::exact('type'),
            )
            ->allowedSorts(
                AllowedSort::custom('videos', new VideosSorter),
            )
            ->when(
                blank($request->input('search')) || $request->input('search') === '*',
                fn (ScoutBuilder $scout) => $scout->defaultSort('videos')
            )
            ->simplePaginate(perPage: 16)
            ->through(fn (Tag $tag) => TagResource::make($tag));
    }
}
