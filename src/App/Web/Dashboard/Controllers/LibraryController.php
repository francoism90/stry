<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Users\Requests\LibraryIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Shared\Responses\CollectionProperties;
use Domain\Users\Enums\LibraryFilter;
use Domain\Users\Scopes\LibraryFilterScope;
use Domain\Videos\Models\Video;
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

    public function __invoke(LibraryIndexRequest $request, CollectionProperties $collection): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Build the video query with the selected list scope
        $builder = Video::query()
            ->tap(new LibraryFilterScope($request->safe()->input('filter', LibraryFilter::Watching)))
            ->simplePaginate(18);

        return Inertia::render('Dashboard/LibraryIndex', [
            'filters' => fn () => LibraryFilter::options(),
            'items' => Inertia::scroll(fn () => VideoResource::collection($builder)),
            $collection,
        ]);
    }
}
