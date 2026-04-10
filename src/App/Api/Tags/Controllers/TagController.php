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
    public function index(): Paginator
    {
        Gate::authorize('viewAny', Tag::class);

        $videosSort = AllowedSort::custom('videos', new VideosSorter);

        return ScoutBuilder::for(Tag::class)
            ->query(fn (TagQueryBuilder $query) => $query->withCount('videos'))
            ->allowedFilters(
                AllowedFilter::exact('type'),
            )
            ->allowedSorts(
                $videosSort,
                AllowedSort::field('ordered', 'name'),
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
            )
            ->defaultSort($videosSort)
            ->simplePaginate(perPage: 16)
            ->through(fn (Tag $tag) => TagResource::make($tag));
    }
}
