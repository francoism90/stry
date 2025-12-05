<?php

declare(strict_types=1);

namespace App\Client\Search\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoOrderScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Apply filters
        $order = $request->safe()->input('sort', VideoOrder::Recommended);

        // Scout builder
        $scout = Video::search($request->safe()->input('search'))
            ->tap(new VideoOrderScope($order))
            ->simplePaginate(12)
            ->through(fn (Video $video) => new VideoResource($video));

        return Inertia::render('Client/LandingIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'sort' => fn () => $order,
            'sorters' => fn () => VideoOrder::options(),
        ]);
    }
}
