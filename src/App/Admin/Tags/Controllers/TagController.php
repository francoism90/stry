<?php

declare(strict_types=1);

namespace App\Admin\Tags\Controllers;

use App\Admin\Tags\Responses\TagResourceProperty;
use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Requests\TagStoreRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagFilterScope;
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
        $search = $request->safe()->input('search');
        $type = $request->safe()->input('type', TagType::Genre);

        // Scout builder
        $scout = Tag::search($search)
            ->tap(new TagFilterScope(type: $type))
            ->simplePaginate(perPage: 16);

        return Inertia::render('Admin/Tags/TagIndex', [
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
            'type' => fn () => $type,
            'types' => fn () => TagType::options(),
        ]);
    }

    public function store(TagStoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Tag::class);

        // Create the tag
        $tag = Tag::create($request->safe()->all());

        // Flash message
        Inertia::flash('message', __('The tag has been created.'));

        return redirect()->route('admin.tags.edit', $tag);
    }

    public function edit(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('Admin/Tags/TagEdit', [
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

        // Flash message
        Inertia::flash('message', __('The tag has been updated.'));

        return back();
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        // Delete the tag
        $tag->deleteOrFail();

        // Flash message
        Inertia::flash('message', __('The tag has been deleted.'));

        return redirect()->route('admin.tags.index');
    }
}
