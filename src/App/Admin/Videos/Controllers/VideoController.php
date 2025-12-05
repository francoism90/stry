<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Admin\Videos\Responses\VideoResourceProperty;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Client\Videos\Responses\VideoProgressProperty;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Enums\VideoSort;
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
        $search = $request->safe()->input('search', '');
        $sort = $request->safe()->input('sort', VideoSort::Relevant);

        // Scout builder
        $scout = Video::search($search)
            ->tap(new VideoSortScope($sort))
            ->simplePaginate(12)
            ->through(fn (Video $video) => new VideoResource($video));

        return Inertia::render('Admin/Videos/VideoIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'search' => fn () => $search,
            'sort' => fn () => $sort,
            'sorters' => fn () => VideoSort::options(),
        ]);
    }

    public function edit(Video $video): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Admin/Videos/VideoEdit', [
            'video' => new VideoResourceProperty($video),
            'progress' => new VideoProgressProperty($video),
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

        return redirect()->route('admin.videos.index');
    }
}
