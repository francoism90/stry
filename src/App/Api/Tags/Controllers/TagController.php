<?php

declare(strict_types=1);

namespace App\Api\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Tags\Scopes\TagFilterScope;
use Domain\Tags\Models\Tag;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class TagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(TagIndexRequest $request): Paginator
    {
        Gate::authorize('viewAny', Tag::class);

        return Tag::search($request->safe()->input('search'))
            ->tap(new TagFilterScope(...$request->safe()->only(['sort'])))
            ->simplePaginate(perPage: 16)
            ->through(fn (Tag $tag) => TagResource::make($tag));
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, Tag $tag): TagResource
    {
        Gate::authorize('update', $tag);

        // $tag = app(UpdateTagDetails::class)->handle($tag, $request->validated());

        return TagResource::make($tag);
    }

    public function destroy(string $id)
    {
        //
    }
}
