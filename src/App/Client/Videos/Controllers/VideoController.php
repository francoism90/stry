<?php

declare(strict_types=1);

namespace App\Client\Videos\Controllers;

use App\Client\Videos\Responses\VideoPlaylistProperty;
use App\Client\Videos\Responses\VideoProgressProperty;
use App\Client\Videos\Responses\VideoQueueProperty;
use App\Client\Videos\Responses\VideoResourceProperty;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
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
        ];
    }

    public function __invoke(Video $video): Response
    {
        Gate::authorize('view', $video);

        // Generate video playlists if they don't exist
        PlaylistVideo::dispatchIf(
            ! $video->hasPlaylist('clip'),
            $video,
        );

        return Inertia::render('Client/Videos/VideoView', [
            'video' => fn () => new VideoResourceProperty(video: $video),
            'playlist' => fn () => new VideoPlaylistProperty(video: $video),
            'progress' => fn () => new VideoProgressProperty(video: $video, user: Auth::user()),
            'queue' => Inertia::defer(fn () => new VideoQueueProperty($video))->deepMerge()->matchOn('data.id'),
        ]);
    }
}
