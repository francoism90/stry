<?php

declare(strict_types=1);

namespace App\Web\Tags\Controllers;

use App\Api\Tags\Resources\TagResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Videos\Scopes\VideoFilterScope;
use Domain\Tags\Models\Tag;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class TagVideoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Tag $tag, VideoIndexRequest $request): Response
    {
        $items = Video::search($request->input('search', ''))
            ->tap(new VideoFilterScope(tags: [$tag->getKey()], sort: $request->input('sort')))
            ->simplePaginate(perPage: 24, page: (int) $request->input('page', 1))
            ->through(fn (Video $video) => VideoResource::make($video));

        return Inertia::render('Tags/TagVideos', [
            'tag' => fn () => TagResource::make($tag->loadCount('videos')),
            'items' => Inertia::defer(fn () => $items)->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function create()
    {
        // Gate::authorize('create', Video::class);

        // return Inertia::render('Videos/VideoCreate', [
        //     //
        // ]);
    }
}
