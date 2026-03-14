<?php

declare(strict_types=1);

namespace App\Web\Search\Controllers;

use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SearchVideosController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(Request $request, string $query = ''): Response
    {
        Gate::authorize('viewAny', Video::class);

        $order = $request->input('order');

        $scout = Video::search($query)
            ->tap(new VideoFilterScope(order: $order))
            ->simplePaginate(perPage: 18);

        return Inertia::render('App/Search/SearchVideos', [
            'search' => fn () => $query,
            'order' => fn () => $order,
            'orders' => fn () => VideoOrder::options(),
            'items' => Inertia::scroll(fn () => VideoResource::collection($scout)),
        ]);
    }
}
