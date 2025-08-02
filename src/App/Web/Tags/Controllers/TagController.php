<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Requests\TagUpdateRequest;
use App\Api\Tags\Resources\TagResource;
use App\Web\Tags\Requests\TagIndexRequest;
use Domain\Tags\Actions\UpdateTagDetails;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(TagIndexRequest $request)
    {
        $items = Tag::query()
            ->cursorPaginate(perPage: 24, cursor: (string) $request->input('page', ''))
            ->through(fn (Tag $tag) => TagResource::make($tag));

        return Inertia::render('Tags/TagIndex', [
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

    public function show(Tag $tag): Response
    {
        Gate::authorize('view', $tag);

        return Inertia::render('Tags/TagView', [
            'tag' => fn () => $tag->toResource(TagResource::class),
        ]);
    }

    public function edit(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return Inertia::render('Tags/TagEdit', [
            'types' => fn () => collect(TagType::cases())->forEnum(),
            'tag' => fn () => $tag->toResource(TagResource::class),
        ]);
    }

    public function update(TagUpdateRequest $request, Tag $tag): RedirectResponse
    {
        app(UpdateTagDetails::class)->handle($tag, $request->validated());

        flash()->success('Tag updated successfully!');

        return back();
    }

    public function destroy(Tag $tag)
    {
        //
    }
}
