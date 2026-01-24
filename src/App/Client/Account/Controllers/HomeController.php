<?php

declare(strict_types=1);

namespace App\Client\Account\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Client\Tags\Responses\TagResourceProperty;
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
        $tag = $request->safe()->input('tag');
        $order = $request->safe()->input('order', VideoOrder::Default);

        // Scout builder
        $scout = Video::search($search ?: '*')
            ->tap(new VideoFilterScope(
                user: $request->user(),
                filter: $filter,
                tag: $tag,
                order: $order,
            ))
            ->paginate(perPage: 18);

        return Inertia::render('Client/Videos/VideoIndex', [
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'orders' => fn () => VideoOrder::options(),
            'tag' => fn () => new TagResourceProperty($tag),
            'filter' => fn () => $filter->label(),
            'order' => fn () => $order,
            'search' => fn () => $search,
        ]);
    }
}
