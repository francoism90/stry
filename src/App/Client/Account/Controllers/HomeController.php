<?php

declare(strict_types=1);

namespace App\Client\Account\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Client\Tags\Responses\TagResourceProperty;
use App\Client\Videos\Responses\VideoFilterProperty;
use App\Client\Videos\Responses\VideoOrdersProperty;
use Domain\Videos\Enums\VideoFilter;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(VideoFilter $filter, VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Apply filters
        $search = $request->safe()->input('search');
        $order = $request->safe()->input('order', VideoOrder::Default);
        $tag = $request->safe()->input('tag');

        // Scout builder
        $scout = Video::search($search)
            ->tap(new VideoFilterScope(
                user: $request->user(),
                tag: $tag,
                filter: $filter,
                order: $order,
            ))
            ->simplePaginate(perPage: 18);

        return Inertia::render('Client/Videos/VideoIndex', [
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'orders' => fn () => new VideoOrdersProperty,
            'tag' => fn () => new TagResourceProperty($tag),
            'filter' => fn () => new VideoFilterProperty($filter),
            'order' => fn () => $order,
            'search' => fn () => $search,
        ]);
    }
}
