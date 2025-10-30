<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Shared\Responses\CollectionFilters;
use App\Web\Videos\Responses\VideoCollectionProperty;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoOrderScope;
use Domain\Videos\Scopes\VideoSearchScope;
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

    public function __invoke(VideoIndexRequest $request, CollectionFilters $filters): Response
    {
        Gate::authorize('viewAny', Video::class);

        $builder = Video::search($request->safe()->input('search'))
            ->tap(new VideoOrderScope($request->safe()->input('order')))
            ->simplePaginate(24);

        return Inertia::render('Dashboard/DashboardIndex', [
            'orders' => fn () => VideoOrder::options(),
            'items' => Inertia::scroll(fn () => VideoResource::collection($builder)),
            $filters,
        ]);
    }
}
