<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Actions\UpdateTagDetails;
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

    public function index(Request $request)
    {
        //
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
            'tag' => fn () => $tag->toResource(TagResource::class),
        ]);
    }

    public function update(Request $request, Tag $tag): RedirectResponse
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
