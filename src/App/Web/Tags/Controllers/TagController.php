<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Tags\Scopes\TagListScope;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Api\Videos\Scopes\VideoFilterScope;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $items = Tag::query()
            ->tap(new TagListScope(type: $request->safe()->input('type')))
            ->cursorPaginate(perPage: 12 * 4, cursor: (string) $request->safe()->input('page', ''))
            ->through(fn (Tag $tag) => TagResource::make($tag));

        return Inertia::render('Tags/TagIndex', [
            'items' => Inertia::defer(fn () => $items)->deepMerge()->matchOn('data.id'),
            'types' => fn () => collect(TagType::cases())->forEnum(),
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function create()
    {
        // Gate::authorize('create', Tag::class);

        // return Inertia::render('Tags/TagCreate', [
        //     //
        // ]);
    }

    public function show(Tag $tag, VideoIndexRequest $request): Response
    {
        Gate::authorize('view', $tag);

        $items = Video::search($request->safe()->input('search', ''))
            ->tap(new VideoFilterScope(tags: [$tag->getKey()], sort: $request->safe()->input('sort')))
            ->simplePaginate(perPage: 24, page: (int) $request->safe()->input('page', 1))
            ->through(fn (Video $video) => VideoResource::make($video));

        return Inertia::render('Tags/TagView', [
            'tag' => fn () => TagResource::make($tag->loadCount('videos')),
            'items' => Inertia::defer(fn () => $items)->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function edit(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('Tags/TagEdit', [
            'types' => fn () => collect(TagType::cases())->forEnum(),
            'tag' => fn () => $tag->append('relates')->toResource(TagResource::class),
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
