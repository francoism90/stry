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

    public function store(Request $request): Response
    {
        abort(404);
    }

    public function create(): Response
    {
        abort(404);
    }

    public function show(Video $video, Playlist $playlist): Response
    {
        Gate::authorize('update', [$video, $playlist]);

        abort(404);
    }

    public function edit(Video $video, Playlist $playlist): Response
    {
        Gate::authorize('update', [$video, $playlist]);

        abort(404);
    }

    public function update(Request $request, Video $video, Playlist $playlist): Response
    {
        Gate::authorize('update', [$video, $playlist]);

        abort(404);
    }

    public function destroy(Video $video, Playlist $playlist): Response
    {
        Gate::authorize('delete', [$video, $playlist]);

        abort(404);
    }
}
