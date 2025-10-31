<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Shared\Responses\CollectionProperties;
use App\Web\Tags\Responses\TagEditProperties;
use App\Web\Tags\Responses\TagViewProperties;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Models\Tag;
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
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(Tag $tag, VideoIndexRequest $request, TagViewProperties $properties, CollectionProperties $collection): Response
    {
        Gate::authorize('view', $tag);

        // Build the video query with search and filtering by tag
        $builder = Video::search($request->safe()->input('search'))
            ->tap(new VideoFilterScope(tags: $tag, filter: $request->safe()->input('filter', VideoOrder::Recommended)))
            ->simplePaginate(24);

        return Inertia::render('Tags/TagView', [
            'filters' => fn () => VideoOrder::options(),
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

        return redirect()->route('tags.index');
    }
}
