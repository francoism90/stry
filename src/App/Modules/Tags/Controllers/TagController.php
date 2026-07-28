<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagStoreRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Tags\Responses\TagResourceProperty;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Enums\TagSorter;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Domain\Videos\Enums\VideoFilter;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoProfileScope;
use Foundation\Http\Controllers\Controller;
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;
use Support\Scout\Filters;
use Support\Scout\Sorts\RecommendedSorter;
use Support\Scout\Sorts\VideosSorter;

class TagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Tag::class);

        // Scout builder
        $videosSort = AllowedSort::custom('videos', new VideosSorter);

        $scout = ScoutBuilder::for(Tag::class)
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
            ->jsonSimplePaginate(defaultSize: 20);

        return Inertia::render('Tags/TagIndex', [
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
            'filters' => fn () => Options::forEnum(TagType::class),
            'sorters' => fn () => Options::forEnum(TagSorter::class),
            'filter' => fn () => $request->input('filter'),
            'sort' => fn () => $request->input('sort'),
            'query' => fn () => $request->input('query'),
        ]);
    }

    public function show(Tag $tag, Request $request): Response
    {
        Gate::authorize('view', $tag);

        // Relevant sort options
        $recommendedSort = AllowedSort::custom('recommended', new RecommendedSorter);

        // Scout builder
        $scout = ScoutBuilder::for(Video::class)
            ->tap(new VideoProfileScope)
            ->whereIn('tagged', [$tag->getKey()])
            ->allowedFilters(
                AllowedFilter::exact('captioned'),
                AllowedFilter::custom('shorts', new Filters\FilterShorts),
                AllowedFilter::custom('tagged', new Filters\FilterTagged),
                AllowedFilter::custom('untagged', new Filters\FilterUntagged),
                AllowedFilter::custom('unseen', new Filters\FilterUnseen),
            )
            ->allowedSorts(
                $recommendedSort,
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
                AllowedSort::field('ordered', 'name'),
                AllowedSort::field('shortest', 'duration'),
                AllowedSort::field('longest', 'duration')->defaultDescending(),
                AllowedSort::field('filesize')->defaultDescending(),
            )
            ->defaultSort($recommendedSort)
            ->jsonSimplePaginate(defaultSize: 16);

        return Inertia::render('Tags/TagView', [
            'tag' => fn () => new TagResourceProperty($tag),
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'filters' => fn () => Options::forEnum(VideoFilter::class),
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
            'filter' => fn () => $request->input('filter', []),
            'sort' => fn () => $request->input('sort'),
            'query' => fn () => $request->input('query'),
        ]);
    }

    public function store(TagStoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Tag::class);

        // Create the tag
        $tag = Tag::create($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $tag->name,
            'description' => __('The tag has been created.'),
            'type' => 'success',
        ]);

        return redirect()->route('tags.show', $tag);
    }

    public function edit(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('Tags/TagEdit', [
            'tag' => fn () => new TagResourceProperty($tag, ['relates', 'description']),
            'types' => fn () => Options::forEnum(TagType::class),
        ]);
    }

    public function update(Tag $tag, TagUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $tag);

        // Update tag details
        app(UpdateTagDetails::class)->handle(
            tag: $tag,
            attributes: $request->safe()->all()
        );

        // Notify the user
        Inertia::flash([
            'title' => (string) $tag->name,
            'description' => __('The tag has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        // Delete the tag
        $tag->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $tag->name,
            'description' => __('The tag has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}
