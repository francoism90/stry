<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Shared\Responses\CollectionProperties;
use App\Web\Tags\Responses\TagEditProperties;
use App\Web\Tags\Responses\TagViewProperties;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Models\Tag;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Domain\Videos\Enums\VideoFilter;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(TagIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Tag::class);

        $scout = Tag::search($request->safe()->input('search'))
            ->query(fn (TagQueryBuilder $query) => $query->withCount('videos'))
            // ->tap(new VideoFilterScope($request))
            ->simplePaginate(18);

        return Inertia::render('Dashboard/TagIndex', [
            'search' => fn () => $request->safe()->input('search', ''),
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
        ]);
    }

    public function show(Tag $tag, VideoIndexRequest $request, TagViewProperties $properties, CollectionProperties $collection): Response
    {
        Gate::authorize('view', $tag);

        // Build the video query with search and filtering by tag
        $builder = Video::search($request->safe()->input('search'))
            ->tap(new VideoFilterScope(tags: $tag, filter: $request->safe()->input('filter', VideoFilter::Recommended)))
            ->simplePaginate(24);

        return Inertia::render('Tags/TagView', [
            'filters' => fn () => VideoFilter::options(),
            'items' => Inertia::scroll(fn () => VideoResource::collection($builder)),
            $properties,
            $collection,
        ]);
    }

    public function edit(Tag $tag, TagEditProperties $properties): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('Tags/TagEdit', [
            $properties,
        ]);
    }

    public function update(TagUpdateRequest $request, Tag $tag): RedirectResponse
    {
        Gate::authorize('update', $tag);

        app(UpdateTagDetails::class)->handle($tag, $request->safe()->all());

        return back();
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        $tag->delete();

        return redirect()->route('explore');
    }
}
