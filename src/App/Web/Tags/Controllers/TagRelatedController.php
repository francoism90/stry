<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Resources\TagResource;
use Domain\Relates\Models\Related;
use Domain\Tags\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagRelatedController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Tag $tag, TagIndexRequest $request): Response
    {
        Gate::authorize('update', $tag);

        $items = Tag::query()
            ->cursorPaginate(perPage: 24, cursor: (string) $request->safe()->input('page', ''))
            ->through(fn (Tag $tag) => TagResource::make($tag));

        return Inertia::render('Tags/TagRelated', [
            'tag' => fn () => $tag->toResource(TagResource::class),
            'items' => Inertia::defer(fn () => $items)->deepMerge()->matchOn('data.id'),
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

    public function show(Tag $tag, Related $related)
    {
        Gate::authorize('update', [$tag, $related]);

        //
    }

    public function edit(Tag $tag, Related $related)
    {
        Gate::authorize('update', [$tag, $related]);

        //
    }

    public function update(Request $request, Tag $tag)
    {
        //
    }

    public function destroy(Tag $tag, Related $related)
    {
        Gate::authorize('delete', [$tag, $related]);

        // $related->delete();

        // return redirect()->route('tags.index');
    }
}
