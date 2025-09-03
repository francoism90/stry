<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Tags\Scopes\TagListScope;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Web\Dashboard\Responses\VideoScoutCollection;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Arr;
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

        $items = Tag::query()
            ->tap(new TagListScope(...$request->safe()->only(['type'])))
            ->simplePaginate(perPage: 24, page: (int) $request->safe()->input('page', 1))
            ->through(fn (Tag $tag) => TagResource::make($tag));

        return Inertia::render('Tags/TagIndex', [
            'items' => Inertia::defer(fn () => $items)->deepMerge()->matchOn('data.id'),
            'types' => fn () => collect(TagType::cases())->forEnum(),
        ]);
    }

    public function store(Request $request): Response
    {
        abort(404);
    }

    public function create(): Response
    {
        abort(404);
    }

    public function show(Tag $tag, VideoIndexRequest $request): Response
    {
        Gate::authorize('view', $tag);

        return Inertia::render('Tags/TagView', [
            'tag' => fn () => $tag->loadCount('videos')->toResource(TagResource::class),
            'items' => Inertia::defer(fn () => new VideoScoutCollection(
                query: $request->safe()->input('search', '*'),
                tags: Arr::wrap($tag->getKey()),
                sort: $request->safe()->input('sort'),
                page: (int) $request->safe()->input('page', 1),
            ))->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function edit(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('Tags/TagEdit', [
            'tag' => fn () => $tag->loadCount('videos')->append('relates')->toResource(TagResource::class),
            'types' => fn () => collect(TagType::cases())->forEnum(),
        ]);
    }

    public function update(TagUpdateRequest $request, Tag $tag): RedirectResponse
    {
        Gate::authorize('update', $tag);

        app(UpdateTagDetails::class)->handle($tag, $request->safe()->all());

        flash()->success('Tag updated successfully!');

        return back();
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        $tag->delete();

        return redirect()->route('tags.index');
    }
}
