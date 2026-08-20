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
use Domain\Videos\Enums\VideoScope;
use Domain\Videos\Enums\VideoSorter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoProfileScope;
use Foundation\Http\Properties\ScoutBuilderProperties;
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

class TagController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Request $request, ScoutBuilderProperties $properties): Response
    {
        Gate::authorize('viewAny', Tag::class);

        // Remember the search term for the global search bar
        if (($query = trim((string) $request->query('query', ''))) !== '') {
            $request->session()->cache()->put('search', $query, now()->addHour());
        }

        // Scout builder
        $defaultSort = AllowedSort::custom('videos', new VideosSorter);

        $scout = ScoutBuilder::for(Tag::class)
            ->query(fn (TagQueryBuilder $query) => $query->withCount('videos')->with('related'))
            ->allowedFilters(
                AllowedFilter::exact('scope', 'type'),
            )
            ->allowedSorts(
                $defaultSort,
                AllowedSort::field('ordered', 'name'),
                AllowedSort::latest('newest', 'created_at'),
                AllowedSort::oldest('oldest', 'created_at'),
            )
            ->defaultSort($defaultSort)
            ->jsonSimplePaginate(defaultSize: 20);

        $scout->getCollection()->each(fn (Tag $tag) => $tag->append(['description', 'relates']));

        return Inertia::render('Tags/TagIndex', [
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
            'scopes' => fn () => Options::forEnum(TagType::class),
            'sorters' => fn () => Options::forEnum(TagSorter::class),
            $properties,
        ]);
    }

    public function show(Tag $tag, ScoutBuilderProperties $properties): Response
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
                AllowedFilter::custom('scope', new Filters\FilterShorts),
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
            'scopes' => fn () => Options::forEnum(VideoScope::class),
            'sorters' => fn () => Options::forEnum(VideoSorter::class),
            $properties,
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
