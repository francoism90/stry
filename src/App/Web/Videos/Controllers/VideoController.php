<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Videos\Responses\VideoCaptionCollection;
use App\Web\Videos\Responses\VideoPlaylistClip;
use App\Web\Videos\Responses\VideoProgress;
use App\Web\Videos\Responses\VideoSimilarCollection;
use App\Web\Videos\Responses\VideoTypeCollection;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoSearchScope;
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
            'search' => $request->safe()->input('search'),
            'filter' => $request->safe()->input('filter'),
            'filters' => fn () => new VideoTypeCollection,
            'items' => Inertia::scroll(fn () => VideoResource::collection(Video::search($request->safe()->input('search'))
                ->tap(new VideoSearchScope(type: $request->safe()->input('filter')))
                ->simplePaginate(24)
            )),
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
            'captions' => fn () => new VideoCaptionCollection($video),
            'playlist' => fn () => new VideoPlaylistClip($video),
            'progress' => fn () => new VideoProgress($video, $request->user()),
            'queue' => Inertia::defer(fn () => new VideoSimilarCollection($video))->deepMerge()->matchOn('data.id'),
        ]);
    }

    public function edit(Video $video, Request $request): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Videos/VideoEdit', [
            'video' => fn () => $video->loadMissing('user')->append(['content', 'titles'])->toResource(VideoResource::class),
            'progress' => fn () => new VideoProgress($video, $request->user()),
        ]);
    }

    public function update(VideoUpdateRequest $request, Video $video): RedirectResponse
    {
        Gate::authorize('update', $video);

        app(UpdateVideoDetails::class)->handle($video, $request->safe()->all());

        return back();
    }

    public function destroy(Video $video): RedirectResponse
    {
        Gate::authorize('delete', $video);

        $video->delete();

        return redirect()->route('videos.index');
    }
}
