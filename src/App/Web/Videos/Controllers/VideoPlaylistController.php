<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Videos\Resources\VideoResource;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoPlaylistController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(Video $video): Response
    {
        Gate::authorize('update', $video);

        return Inertia::render('Videos/VideoPlaylists', [
            'video' => fn () => $video->toResource(VideoResource::class),
            'playlists' => fn () => [],
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

    public function show(Video $video, Playlist $playlist)
    {
        Gate::authorize('update', [$video, $playlist]);

        //
    }

    public function edit(Video $video, Playlist $playlist)
    {
        Gate::authorize('update', [$video, $playlist]);

        //
    }

    public function update(Request $request, Video $video, Playlist $playlist)
    {
        //
    }

    public function destroy(Video $video, Playlist $playlist)
    {
        Gate::authorize('delete', [$video, $playlist]);

        // $playlist->delete();

        // return redirect()->route('videos.index');
    }
}
