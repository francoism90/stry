<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SearchTagsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request, string $query = ''): Response
    {
        Gate::authorize('viewAny', Tag::class);

        $scout = Tag::search($query)
            ->tap(new TagFilterScope)
            ->simplePaginate(perPage: 16);

        return Inertia::render('App/Search/SearchTags', [
            'search' => $query,
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
        ]);
    }
}
