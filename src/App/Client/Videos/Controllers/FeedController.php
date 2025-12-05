<?php

declare(strict_types=1);

namespace App\Client\Videos\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Enums\VideoList;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoOrderScope;
use Domain\Videos\Scopes\VideoListScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller implements HasMiddleware
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
        $list = $request->safe()->input('list', VideoList::Recommended);

        // Scout builder
        $scout = Video::search($request->safe()->input('search'))
            ->tap(new VideoListScope($list))
            ->simplePaginate(12)
            ->through(fn (Video $video) => new VideoResource($video));

        return Inertia::render('Client/Videos/FeedIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'list' => fn () => $list,
            'lists' => fn () => VideoList::options(),
        ]);
    }
}
