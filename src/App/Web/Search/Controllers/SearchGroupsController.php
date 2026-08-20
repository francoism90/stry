<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Groups\Resources\GroupResource;
use Domain\Groups\Enums\GroupSorter;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
use Foundation\Http\Properties\ScoutBuilderProperties;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;

class SearchGroupsController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function __invoke(ScoutBuilderProperties $properties, string $query = ''): Response
    {
        Gate::authorize('viewAny', Group::class);

        $updatedSort = AllowedSort::field('updated', 'updated_at')->defaultDescending();

        $scout = ScoutBuilder::for(Group::search($query))
            ->query(fn (GroupQueryBuilder $builder) => $builder->withCount('groupables'))
            ->allowedFilters(
                AllowedFilter::exact('scope', 'type'),
            )
            ->allowedSorts(
                AllowedSort::field('name'),
                AllowedSort::field('videos', 'groupables')->defaultDescending(),
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                $updatedSort,
            )
            ->defaultSort($updatedSort)
            ->jsonSimplePaginate(defaultSize: 16);

        return Inertia::render('Search/SearchCollections', [
            'search' => fn () => $query,
            'items' => Inertia::scroll(fn () => GroupResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(GroupType::class),
            'sorters' => fn () => Options::forEnum(GroupSorter::class),
            $properties,
        ]);
    }
}
