<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Shared\Responses\CollectionProperties;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(VideoIndexRequest $request, CollectionProperties $collection): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Build the video query with search and ordering
        $builder = Video::search($request->safe()->input('search'))
            ->tap(new VideoFilterScope($request->safe()->input('filter', VideoOrder::Recommended)))
            ->simplePaginate(18);

        return Inertia::render('Dashboard/DashboardIndex', [
            'filters' => fn() => VideoOrder::options(),
            'items' => Inertia::scroll(fn() => VideoResource::collection($builder)),
            $collection,
        ]);
    }
}
