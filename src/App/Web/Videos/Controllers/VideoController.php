<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Media\Resources\MediaResource;
use App\Api\Playlists\Resources\PlaylistResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Dashboard\Responses\VideoQueryCollection;
use App\Web\Dashboard\Responses\VideoResourceCollection;
use Domain\Videos\Actions\GetSimilarVideos;
use Domain\Videos\Actions\GetVideoProgress;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(VideoIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Video::class);

        return Inertia::render('Videos/VideoIndex', [
            'items' => Inertia::defer(fn () => new VideoQueryCollection(
                type: $request->safe()->input('list'),
                page: (int) $request->safe()->input('page', 1),
                perPage: 24,
            ))->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function store(Request $request): Response
    {
        Gate::authorize('create', Video::class);

        abort(404);
    }

    public function create(): Response
    {
        Gate::authorize('create', Video::class);

        abort(404);
    }

    public function show(Video $video, Request $request): Response
    {
        Gate::authorize('view', $video);

        // Generate video playlists if they don't exist
        PlaylistVideo::dispatchIf(
            ! $video->hasPlaylist('clip') || ! $video->hasPlaylist('preview'),
            $video
        );

        return Inertia::render('Videos/VideoView', [
            'video' => fn () => $video->append(['content', 'titles'])->toResource(VideoResource::class),
            'captions' => fn () => $video->getCaptionCollection()?->toResourceCollection(MediaResource::class),
            'playlist' => fn () => $video->getFirstPlaylist('clip')?->toResource(PlaylistResource::class),
            'progress' => fn () => app(GetVideoProgress::class)->handle($video, $request->user()),
            'queue' => Inertia::defer(fn () => new VideoResourceCollection(items: app(GetSimilarVideos::class)->handle($video)))->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function edit(Video $video, Request $request): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Videos/VideoEdit', [
            'video' => fn () => $video->append(['content', 'titles'])->toResource(VideoResource::class),
            'progress' => fn () => app(GetVideoProgress::class)->handle($video, $request->user()),
        ]);
    }

    public function update(VideoUpdateRequest $request, Video $video): RedirectResponse
    {
        Gate::authorize('update', $video);

        app(UpdateVideoDetails::class)->handle($video, $request->safe()->all());

        flash()->success('Video updated successfully!');

        return back();
    }

    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);

        $video->delete();

        return redirect()->route('videos.index');
    }
}
