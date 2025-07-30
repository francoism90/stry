<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Videos\Resources\VideoResource;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
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

    public function show(Playlist $playlist): Response
    {
        Gate::authorize('view', $playlist);

        return Inertia::render('Videos/VideoPlaylists', [
            //
        ]);
    }

    public function edit(Request $request, Playlist $playlist): Response
    {
        Gate::authorize('update', $playlist);

        return Inertia::render('Videos/VideoEdit', [
            'item' => fn () => $playlist->load('tags')->append(['content', 'titles'])->toResource(VideoResource::class),
        ]);
    }

    public function update(Request $request, Playlist $playlist): RedirectResponse
    {
        // app(UpdateVideoDetails::class)->handle($playlist, $request->validated());

        flash()->success('Video updated successfully!');

        return back();
    }

    public function destroy(Playlist $playlist)
    {
        //
    }
}
