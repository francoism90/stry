<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Tags\Requests\TagIndexRequest;
use App\Api\Tags\Resources\TagResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Shared\Responses\CollectionFilters;
use App\Web\Shared\Responses\CollectionProperties;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagFilterScope;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoOrderScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ExploreController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(TagIndexRequest $request, CollectionProperties $collection): Response
    {
        Gate::authorize('viewAny', Tag::class);

        // Build the tag query with search and filtering
        $builder = Tag::search($request->safe()->input('search'))
            ->tap(new TagFilterScope($request->safe()->input('filter')))
            ->simplePaginate(16);

        return Inertia::render('Dashboard/ExploreIndex', [
            'filters' => fn () => TagType::options(),
            'items' => Inertia::scroll(fn () => TagResource::collection($builder)),
            $collection,
        ]);
    }
}
