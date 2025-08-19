<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Media\Resources\MediaResource;
use App\Api\Playlists\Resources\PlaylistResource;
use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Api\Videos\Scopes\VideoListScope;
use Domain\Videos\Actions\GetSimilarVideos;
use Domain\Videos\Actions\GetVideoStartTime;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Events\VideoHasBeenViewedEvent;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $items = Video::query()
            ->tap(new VideoListScope(...$request->safe()->only(['list', 'tags'])))
            ->cursorPaginate(perPage: 24, cursorName: 'page', cursor: $request->safe()->input('page'))
            ->through(fn (Video $video) => VideoResource::make($video));

        return Inertia::render('Videos/VideoIndex', [
            'items' => Inertia::defer(fn () => $items)->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Video::class);
    }

    public function create()
    {
        Gate::authorize('create', Video::class);

        // return Inertia::render('Videos/VideoCreate', [
        //     //
        // ]);
    }

    public function show(Video $video): Response
    {
        Gate::authorize('view', $video);

        VideoHasBeenViewedEvent::dispatchIf(! $video->hasPlaylists('clip'), $video);

        return Inertia::render('Videos/VideoView', [
            'video' => fn () => $video->append(['content', 'titles'])->toResource(VideoResource::class),
            'captions' => fn () => $video->getCaptionCollection()?->toResourceCollection(MediaResource::class),
            'playlist' => fn () => $video->getFirstPlaylist('clip')?->toResource(PlaylistResource::class),
            'starts' => fn () => app(GetVideoStartTime::class)->handle($video, Auth::user()),
            'queue' => Inertia::defer(fn () => app(GetSimilarVideos::class)->handle($video))->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function edit(Video $video): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Videos/VideoEdit', [
            'video' => fn () => $video->load('tags')->append(['content', 'titles'])->toResource(VideoResource::class),
            'starts' => fn () => app(GetVideoStartTime::class)->handle($video, Auth::user()),
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
