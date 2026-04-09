<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Groups\Resources\GroupResource;
use Domain\Groups\Enums\GroupSorter;
use Domain\Groups\Models\Group;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
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

class SearchGroupsController extends Controller implements HasMiddleware
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
        Gate::authorize('viewAny', Group::class);

        $updatedSort = AllowedSort::field('updated', 'updated_at')->defaultDescending();

        $scout = ScoutBuilder::for(Group::search($query))
            ->query(fn (GroupQueryBuilder $builder) => $builder->withCount('groupables'))
            ->allowedFilters(
                AllowedFilter::exact('type'),
            )
            ->allowedSorts(
                AllowedSort::field('name'),
                AllowedSort::field('videos', 'groupables')->defaultDescending(),
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                $updatedSort,
            )
            ->defaultSort($updatedSort)
            ->jsonSimplePaginate(defaultSize: 24);

        return Inertia::render('App/Search/SearchCollections', [
            'search' => fn () => $query,
            'sort' => fn () => $request->input('sort'),
            'sorters' => fn () => Options::forEnum(GroupSorter::class),
            'items' => Inertia::scroll(fn () => GroupResource::collection($scout)),
        ]);
    }
}
