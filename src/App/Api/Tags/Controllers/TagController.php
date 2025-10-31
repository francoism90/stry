<?php

declare(strict_types=1);

namespace App\Api\Tags\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

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

        return Tag::search($request->safe()->input('search'))
            ->tap(new TagFilterScope(filter: $request->safe()->input('filter', 'all')))
            ->simplePaginate(perPage: 16)
            ->through(fn (Tag $tag) => TagResource::make($tag));
    }
}
