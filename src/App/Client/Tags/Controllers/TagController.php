<?php

declare(strict_types=1);

namespace App\Client\Tags\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Client\Tags\Responses\TagPlaylistProperty;
use App\Client\Tags\Responses\TagProgressProperty;
use App\Client\Tags\Responses\TagQueueProperty;
use App\Client\Tags\Responses\TagResourceProperty;
use Domain\Tags\Jobs\PlaylistTag;
use Domain\Tags\Models\Tag;
use Domain\Videos\Enums\VideoSort;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoSortScope;
use Domain\Videos\Scopes\VideoTagScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function __invoke(Tag $tag, VideoIndexRequest $request): Response
    {
        Gate::authorize('view', $tag);

        // Apply filters
        $search = $request->safe()->input('search', '');
        $sort = $request->safe()->input('sort', VideoSort::Relevant);

        // Scout builder
        $scout = Video::search($search)
            ->tap(new VideoTagScope($tag))
            ->tap(new VideoSortScope($sort))
            ->simplePaginate(16)
            ->through(fn (Video $video) => new VideoResource($video));

        return Inertia::render('Client/Videos/LibraryIndex', [
            'tag' => fn () => new TagResourceProperty($tag),
            'items' => Inertia::scroll(fn () => $scout),
            'search' => fn () => $search,
            'sort' => fn () => $sort,
            'sorters' => fn () => VideoSort::options(),
        ]);
    }
}
