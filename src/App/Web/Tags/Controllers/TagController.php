<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Requests\TagStoreRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Tags\Responses\TagResourceProperty;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagFilterScope;
use Domain\Videos\Enums\VideoOrder;
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
            new Middleware('precognitive'),
        ];
    }

    public function index(TagIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Tag::class);

        // Apply filters
        $type = $request->safe()->input('type');

        // Scout builder
        $scout = Tag::search()
            ->tap(new TagFilterScope(type: $type))
            ->simplePaginate(perPage: 16);

        return Inertia::render('App/Tags/TagIndex', [
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
            'type' => fn () => $type,
            'types' => fn () => TagType::options(),
        ]);
    }

    public function show(Tag $tag, VideoIndexRequest $request): Response
    {
        Gate::authorize('view', $tag);

        // Scout builder
        $scout = Video::search()
            ->tap(new VideoFilterScope(
                tag: $tag,
                order: $request->safe()->input('order'),
            ))
            ->simplePaginate(perPage: 18);

        return Inertia::render('App/Tags/TagView', [
            'tag' => fn () => new TagResourceProperty($tag, ['relates']),
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'order' => fn () => $request->safe()->input('order'),
            'orders' => fn () => VideoOrder::options(),
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
        ]);

        return back();
    }

    public function edit(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('App/Tags/TagEdit', [
            'tag' => fn () => new TagResourceProperty($tag),
            'types' => fn () => TagType::options(),
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
        ]);

        return back();
    }
}
