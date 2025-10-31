<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Videos\Requests\VideoUpdateRequest;
use App\Web\Videos\Responses\VideoEditProperties;
use App\Web\Videos\Responses\VideoPlaylistProperty;
use App\Web\Videos\Responses\VideoQueueProperty;
use App\Web\Videos\Responses\VideoViewProperties;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
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
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function show(Video $video, VideoViewProperties $properties): Response
    {
        Gate::authorize('view', $video);

        // Generate video playlists if they don't exist
        PlaylistVideo::dispatchIf(
            ! $video->hasPlaylist('clip') || ! $video->hasPlaylist('preview'),
            $video
        );

        return Inertia::render('Videos/VideoView', [
            'playlist' => Inertia::defer(fn () => new VideoPlaylistProperty($video)),
            'queue' => Inertia::defer(fn () => new VideoQueueProperty($video))->deepMerge()->matchOn('data.id'),
            $properties,
        ]);
    }

    public function edit(Video $video, VideoEditProperties $properties): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Videos/VideoEdit', [
            $properties,
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
