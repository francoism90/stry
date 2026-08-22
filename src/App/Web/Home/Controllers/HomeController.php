<?php

declare(strict_types=1);

namespace App\Web\Home\Controllers;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoScope;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Filters\VideoScopeFilter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoProfileScope;
use Foundation\Http\Properties\ScoutBuilderProperties;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;
use Support\Scout\Filters;
use Support\Scout\Sorts\RecommendedSorter;

class HomeController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(): Response
    {
        // Relevant sort options
        $defaultSort = AllowedSort::custom('recommended', new RecommendedSorter);

        // Scout builder
        $scout = ScoutBuilder::for(Video::class)
            ->tap(new VideoProfileScope)
            ->allowedFilters(
                AllowedFilter::exact('captioned'),
                AllowedFilter::custom('scope', new VideoScopeFilter),
                AllowedFilter::custom('tagged', new Filters\FilterTagged),
            )
            ->allowedSorts(
                $defaultSort,
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                AllowedSort::field('ordered', 'title'),
                AllowedSort::field('shortest', 'duration'),
                AllowedSort::field('longest', 'duration')->defaultDescending(),
            )
            ->defaultSort($defaultSort)
            ->jsonSimplePaginate(defaultSize: 16);

        return Inertia::render('Videos/VideoIndex', [
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(VideoScope::class),
            'sorters' => fn () => Options::forEnum(VideoSorter::class)->except(VideoSorter::Filesize),
            new ScoutBuilderProperties('videos'),
        ]);
    }
}
