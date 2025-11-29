<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use Domain\Videos\Enums\VideoSort;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoSortScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function index(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        // Get filters
        $sort = $request->safe()->input('sort', VideoSort::Recommended);

        // Build query
        $scout = Video::search($request->safe()->input('search'))
            ->tap(new VideoSortScope($sort))
            ->paginate(12)
            ->through(fn (Video $video) => new VideoResource($video));

        return Inertia::render('Admin/VideoIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'sort' => fn () => $sort,
            'sorters' => fn () => VideoSort::options(),
        ]);
    }
}
