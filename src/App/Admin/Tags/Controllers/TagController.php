<?php

declare(strict_types=1);

namespace App\Admin\Tags\Controllers;

use App\Admin\Tags\Responses\TagResourceProperty;
use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagTypeScope;
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
        $search = $request->safe()->input('search', '');
        $type = $request->safe()->input('type', TagType::Genre);

        // Scout builder
        $scout = Tag::search($request->safe()->input('search'))
            ->tap(new TagTypeScope($type))
            ->simplePaginate(16)
            ->through(fn (Tag $tag) => new TagResource($tag));

        return Inertia::render('Admin/Tags/TagIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'type' => fn () => $type,
            'types' => fn () => TagType::options(),
        ]);
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

        app(UpdateTagDetails::class)->handle(
            tag: $tag,
            attributes: $request->safe()->all()
        );

        return back();
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize('delete', $tag);

        // Delete the tag
        $tag->deleteOrFail();

        return redirect()->route('admin.tags.index');
    }
}
