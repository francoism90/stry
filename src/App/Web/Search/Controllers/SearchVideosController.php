<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoScope;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoProfileScope;
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
use Support\Scout\Filters;
use Support\Scout\Sorts\RecommendedSorter;

class SearchVideosController implements HasMiddleware
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
        Gate::authorize('viewAny', Video::class);

        // Relevant sort options
        $recommendedSort = AllowedSort::custom('recommended', new RecommendedSorter);

        // Scout builder
        $scout = ScoutBuilder::for(Video::search($query))
            ->tap(new VideoProfileScope)
            ->allowedFilters(
                AllowedFilter::exact('captioned'),
                AllowedFilter::custom('scope', new Filters\FilterShorts),
                AllowedFilter::custom('tagged', new Filters\FilterTagged),
                AllowedFilter::custom('untagged', new Filters\FilterUntagged),
                AllowedFilter::custom('unseen', new Filters\FilterUnseen),
            )
            ->allowedSorts(
                $recommendedSort,
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                AllowedSort::field('ordered', 'title'),
                AllowedSort::field('shortest', 'duration'),
                AllowedSort::field('longest', 'duration')->defaultDescending(),
                AllowedSort::field('filesize')->defaultDescending(),
            )
            ->defaultSort($recommendedSort)
            ->jsonSimplePaginate(defaultSize: 16);

        return Inertia::render('Search/SearchVideos', [
            'search' => fn () => $query,
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(VideoScope::class),
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
            $properties,
        ]);
    }
}
