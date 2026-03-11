<?php

declare(strict_types=1);

namespace App\Admin\Videos\Controllers;

use App\Admin\Playlists\Responses\PlaylistResourceProperty;
use App\Admin\Videos\Responses\VideoResourceProperty;
use App\Api\Playlists\Requests\PlaylistIndexRequest;
use App\Api\Playlists\Requests\PlaylistUpdateRequest;
use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VideoPlaylistController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function index(PlaylistIndexRequest $request, Video $video): Response
    {
        Gate::authorize('viewAny', Playlist::class);

        // Fetch playlists for the video
        $playlists = $video->playlists()->simplePaginate(16);

        return Inertia::render('Admin/Videos/Playlists/PlaylistIndex', [
            'video' => fn () => new VideoResourceProperty($video),
            'items' => Inertia::scroll(fn () => PlaylistResource::collection($playlists)),
        ]);
    }

    public function edit(Video $video, Playlist $playlist): Response
    {
        Gate::authorize('update', $playlist);

        return Inertia::render('Admin/Videos/Playlists/PlaylistEdit', [
            'video' => fn () => new VideoResourceProperty($video),
            'playlist' => fn () => new PlaylistResourceProperty($playlist),
        ]);
    }

    public function update(PlaylistUpdateRequest $request, Video $video, Playlist $playlist): RedirectResponse
    {
        Gate::authorize('update', $playlist);

        // Update the playlist with validated data
        $playlist->updateOrFail($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $playlist->file_name,
            'description' => __('The playlist has been updated.'),
        ]);

        return back();
    }

    public function destroy(Video $video, Playlist $playlist): RedirectResponse
    {
        Gate::authorize('delete', $playlist);

        // Delete the playlist
        $playlist->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $playlist->file_name,
            'description' => __('The playlist has been deleted.'),
        ]);

        return back();
    }
}
