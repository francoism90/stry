<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Videos\Responses\VideoPlaylistProperty;
use App\Web\Videos\Responses\VideoProgressProperty;
use App\Web\Videos\Responses\VideoQueueProperty;
use App\Web\Videos\Responses\VideoResourceProperty;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(VideoIndexRequest $request): Response
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
            'orders' => fn () => VideoOrder::options(),
        ]);
    }

    public function show(Video $video): Response
    {
        Gate::authorize('view', $video);

        // Dispatch the job to create a playlist if necessary
        PlaylistVideo::dispatchIf(
            ! $video->hasPlaylist(),
            $video,
        );

        return Inertia::render('App/Videos/VideoView', [
            'video' => fn () => new VideoResourceProperty(video: $video),
            'playlist' => fn () => new VideoPlaylistProperty(video: $video),
            'progress' => fn () => new VideoProgressProperty(video: $video, user: Auth::user()),
            'queue' => Inertia::defer(fn () => new VideoQueueProperty($video))->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function edit(Video $video): Response
    {
        Gate::authorize('update', $video);

        // Define the attributes to append to the video resource
        $appends = [
            'titles',
            'content',
            'summary',
            'snapshot',
            'filesize',
        ];

        return Inertia::render('App/Videos/VideoEdit', [
            'video' => fn () => new VideoResourceProperty($video, $appends),
            'progress' => fn () => new VideoProgressProperty($video, Auth::user()),
        ]);
    }

    public function update(Video $video, VideoUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $video);

        // Update video details
        app(UpdateVideoDetails::class)->handle(
            video: $video,
            attributes: $request->safe()->all()
        );

        // Notify the user
        Inertia::flash([
            'title' => (string) $video->name,
            'description' => __('The video has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);

        // Delete the video
        $video->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $video->name,
            'description' => __('The video has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}
