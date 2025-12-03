<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Client\Videos\Responses\VideoEditProperties;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoSortScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
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

        // Apply filters
        $sort = $request->safe()->input('sort', VideoOrder::Recommended);

        // Scout builder
        $scout = Video::search($request->safe()->input('search'))
            ->tap(new VideoSortScope($sort))
            ->simplePaginate(12)
            ->through(fn (Video $video) => new VideoResource($video));

        return Inertia::render('Admin/Videos/VideoIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'sort' => fn () => $sort,
            'sorters' => fn () => VideoOrder::options(),
        ]);
    }

    public function edit(Video $video, VideoEditProperties $properties): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Admin/Videos/VideoEdit', [
            $properties
        ]);
    }

     public function update(Video $video, VideoUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $video);

        app(UpdateVideoDetails::class)->handle(
            video: $video,
            attributes: $request->safe()->all()
        );

        return back();
    }

    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);

        // Delete the video
        $video->deleteOrFail();

        return back();
    }
}
