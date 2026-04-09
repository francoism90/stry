<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoProfileScope;
use Foundation\Http\Controllers\Controller;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;
use Support\Scout\Sorts\RecommendedSorter;

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

        $scout = ScoutBuilder::for(Video::search($query))
            ->tap(new VideoProfileScope)
            ->allowedSorts(
                AllowedSort::custom('recommended', new RecommendedSorter),
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                AllowedSort::field('ordered', 'name'),
                AllowedSort::field('shortest', 'duration'),
                AllowedSort::field('longest', 'duration')->defaultDescending(),
                AllowedSort::field('filesize')->defaultDescending(),
            )
            ->defaultSort('recommended')
            ->jsonSimplePaginate(defaultSize: 24);

        return Inertia::render('App/Search/SearchVideos', [
            'search' => fn () => $query,
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'sort' => fn () => $request->input('sort'),
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
        ]);
    }
}
