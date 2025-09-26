<?php

declare(strict_types=1);

namespace App\Api\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagSearchScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Laravel\Scout\Builder;

class TagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(TagIndexRequest $request): Paginator
    {
        Gate::authorize('viewAny', Tag::class);

        return Tag::search($request->safe()->input('search', '*'))
            ->tap(new TagSearchScope(sort: $request->safe()->input('sort'), type: $request->safe()->input('type')))
            ->simplePaginate(perPage: 16, page: (int) $request->safe()->input('page', 1))
            ->through(fn (Tag $tag) => TagResource::make($tag));
    }

    public function store(Request $request): Response
    {
        Gate::authorize('create', Tag::class);

        return response()->noContent();
    }

    public function show(Tag $tag): Response
    {
        Gate::authorize('view', $tag);

        return response()->noContent();
    }

    public function update(Tag $tag): Response
    {
        Gate::authorize('update', $tag);

        return response()->noContent();
    }

    public function destroy(Tag $tag): Response
    {
        Gate::authorize('delete', $tag);

        return response()->noContent();
    }
}
