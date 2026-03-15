<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Enums\TagType;
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

        $type = $request->input('type');

        $scout = Tag::search($query)
            ->tap(new TagFilterScope(type: $type))
            ->simplePaginate(perPage: 36);

        return Inertia::render('App/Search/SearchTags', [
            'search' => fn () => $query,
            'type' => fn () => $type,
            'types' => fn () => TagType::options(),
            'items' => Inertia::scroll(fn () => TagResource::collection($scout)),
        ]);
    }
}
