<?php

declare(strict_types=1);

namespace App\Client\Account\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoFilter;
use Domain\Videos\Enums\VideoSort;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Domain\Videos\Scopes\VideoSortScope;
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
        $sort = $request->safe()->input('sort', VideoSort::Recommended);

        // Scout builder
        $scout = Video::search($search)
            ->tap(new VideoFilterScope($filter))
            ->tap(new VideoSortScope($sort))
            ->simplePaginate(12)
            ->through(fn (Video $video) => new VideoResource($video));

        return Inertia::render('Client/Videos/VideoIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'filter' => fn () => $filter->label(),
            'sorters' => fn () => VideoSort::options(),
            'search' => fn () => $search,
            'sort' => fn () => $sort,
        ]);
    }
}
