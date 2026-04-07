<?php

declare(strict_types=1);

namespace App\Web\Account\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelOptions\Options;

class HomeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Scout builder
        $scout = Video::search()
            ->tap(new VideoFilterScope(
                order: $request->safe()->input('order'),
            ))
            ->simplePaginate(perPage: 24);

        return Inertia::render('App/Videos/VideoIndex', [
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
            'order' => fn () => $request->safe()->input('order'),
            'orders' => fn () => Options::forEnum(VideoOrder::class),
        ]);
    }
}
