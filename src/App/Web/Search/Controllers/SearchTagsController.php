<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Enums\TagSorter;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Foundation\Http\Controllers\Controller;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;
use Support\Scout\Sorts\VideosSorter;

class SearchTagsController extends Controller implements HasMiddleware
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
        Gate::authorize('viewAny', Tag::class);

        $videosSort = AllowedSort::custom('videos', new VideosSorter);

        $scout = ScoutBuilder::for(Tag::search($query))
            ->query(fn (TagQueryBuilder $builder) => $builder->withCount('videos'))
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
            ->jsonSimplePaginate(defaultSize: 20);

        return Inertia::render('App/Search/SearchTags', [
            'search' => fn () => $query,
            'sort' => fn () => $request->input('sort'),
            'type' => fn () => $request->input('type'),
            'sorters' => fn () => Options::forEnum(TagSorter::class),
            'types' => fn () => Options::forEnum(TagType::class),
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
        ]);
    }
}
