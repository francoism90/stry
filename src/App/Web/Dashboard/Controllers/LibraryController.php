<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Shared\Responses\CollectionProperties;
use Domain\Videos\Enums\VideoList;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoListScope;
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

    public function __invoke(VideoIndexRequest $request, CollectionProperties $collection): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Build the video query with the selected list scope
        $builder = Video::query()
            ->tap(new VideoListScope($request->safe()->input('filter', 'watching')))
            ->simplePaginate(18);

        return Inertia::render('Dashboard/LibraryIndex', [
            'filters' => fn () => VideoList::options(),
            'items' => Inertia::scroll(fn () => VideoResource::collection($builder)),
            $collection,
        ]);
    }
}
