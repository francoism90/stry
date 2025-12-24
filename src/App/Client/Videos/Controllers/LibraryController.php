<?php

declare(strict_types=1);

namespace App\Client\Videos\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoList;
use Domain\Videos\Enums\VideoSort;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoListScope;
use Domain\Videos\Scopes\VideoSortScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LibraryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Apply filters
        $search = $request->safe()->input('search');
        $filter = $request->safe()->input('filter', VideoList::All);
        $sort = $request->safe()->input('sort', VideoSort::Relevant);

        // Scout builder
        $scout = Video::search($request->safe()->input('search'))
            ->tap(new VideoListScope($filter))
            ->tap(new VideoSortScope($sort))
            ->simplePaginate(12)
            ->through(fn (Video $video) => new VideoResource($video));

        return Inertia::render('Client/Videos/LibraryIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'sorters' => fn () => VideoSort::options(),
            'filters' => fn () => VideoList::options(),
            'search' => fn () => $search,
            'filter' => fn () => $filter,
            'sort' => fn () => $sort,
        ]);
    }
}
