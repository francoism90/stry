<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Tags\Responses\TagTypeCollection;
use App\Web\Videos\Responses\VideoTypeCollection;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagSearchScope;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoSearchScope;
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

        return Inertia::render('Tags/TagIndex', [
            'filter' => $request->safe()->input('filter'),
            'search' => $request->safe()->input('search'),
            'filters' => fn () => new TagTypeCollection,
            'items' => Inertia::scroll(fn () => TagResource::collection(Tag::search($request->safe()->input('search'))
                ->tap(new TagSearchScope(type: $request->safe()->input('filter')))
                ->simplePaginate(48)
            )),
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
            'search' => $request->safe()->input('search'),
            'filter' => $request->safe()->input('filter'),
            'filters' => fn () => new VideoTypeCollection,
            'items' => Inertia::scroll(fn () => VideoResource::collection(Video::search($request->safe()->input('search'))
                ->tap(new VideoSearchScope(tags: [$tag->getKey()], type: $request->safe()->input('filter')))
                ->simplePaginate(24))),
        ]);
    }

    public function edit(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('Tags/TagEdit', [
            'tag' => fn () => $tag->loadCount('videos')->append('relates')->toResource(TagResource::class),
            'types' => fn () => new TagTypeCollection,
        ]);
    }

    public function update(TagUpdateRequest $request, Tag $tag): RedirectResponse
    {
        Gate::authorize('update', $tag);

        defer(fn () => app(UpdateTagDetails::class)->handle($tag, $request->safe()->all()));

        return back();
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        $tag->delete();

        return redirect()->route('tags.index');
    }
}
