<?php

declare(strict_types=1);

namespace App\Client\Landing\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Models\Video;
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

    public function __invoke(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        $scout = Video::search($request->safe()->input('search'))
            // ->tap(new VideoFilterScope($request))
            ->paginate(12);

        return Inertia::render('Client/DashboardIndex', [
            'search' => fn () => $request->safe()->input('search', ''),
            'items' => fn () => VideoResource::collection($scout),
        ]);
    }
}
