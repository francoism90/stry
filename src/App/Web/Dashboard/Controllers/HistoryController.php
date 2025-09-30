<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class HistoryController implements HasMiddleware
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

        return Inertia::render('Dashboard/HistoryIndex', [
            'search' => $request->safe()->input('search'),
            'items' => Inertia::scroll(fn () => VideoResource::collection(Video::query()
                ->tap(new VideoFilterScope(type: 'watching'))
                ->simplePaginate(24)
            )),
        ]);
    }
}
