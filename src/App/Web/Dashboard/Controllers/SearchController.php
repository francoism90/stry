<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Videos\Scopes\VideoFilterScope;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller implements HasMiddleware
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
        $items = Video::search($request->input('search', ''))
            ->tap(new VideoFilterScope(sort: $request->input('sort')))
            ->simplePaginate(perPage: 24, page: (int) $request->input('page', 1))
            ->through(fn ($video) => VideoResource::make($video));

        return Inertia::render('Dashboard/SearchIndex', [
            'search' => fn () => $request->input('search'),
            'items' => Inertia::defer(fn () => $request->filled('search') ? $items : [])->deepMerge()->matchOn('data.id'),
        ]);
    }
}
