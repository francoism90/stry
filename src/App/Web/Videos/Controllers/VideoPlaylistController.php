<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Playlists\Requests\PlaylistIndexRequest;
use App\Api\Playlists\Requests\PlaylistStoreRequest;
use App\Api\Playlists\Requests\PlaylistUpdateRequest;
use App\Api\Playlists\Resources\PlaylistResource;
use App\Web\Videos\Responses\VideoResourceProperty;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Jobs\PlaylistVideo;
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
        $playlists = $video
            ->playlists()
            ->simplePaginate(perPage: 16);

        return Inertia::render('App/Videos/Playlists/PlaylistIndex', [
            'video' => fn () => new VideoResourceProperty($video, ['filesize']),
            'items' => Inertia::scroll(fn () => PlaylistResource::collection($playlists)),
        ]);
    }

    public function store(PlaylistStoreRequest $request, Video $video): RedirectResponse
    {
        Gate::authorize('create', Playlist::class);

        $type = PlaylistType::from($request->safe()->string('type')->value());

        PlaylistVideo::dispatch($video, $type);

        Inertia::flash([
            'title' => (string) $video->name,
            'description' => __('Queued for playlist generation.'),
            'type' => 'info',
        ]);

        return back();
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
            'type' => 'success',
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
            'type' => 'warning',
        ]);

        return back();
    }
}
