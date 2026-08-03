<?php

declare(strict_types=1);

namespace App\Modules\Tags\Controllers;

use App\Modules\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Spatie\ResponseCache\Attributes\FlexibleCache;
use Support\Scout\Sorts\VideosSorter;

<<<<<<< HEAD:src/App/Modules/Tags/Controllers/TagFilterController.php
class TagFilterController implements HasMiddleware
=======
class TagController implements HasMiddleware
>>>>>>> b8d034dd (Refactor controllers to remove inheritance from Foundation\Http\Controllers\Controller):src/App/Api/Tags/Controllers/TagController.php
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('precognitive'),
        ];
    }

    #[FlexibleCache(lifetime: 60 * 60, grace: 5 * 60, tags: ['tags'])]
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
