<?php

declare(strict_types=1);

namespace App\Web\Videos\Controllers;

use App\Api\Playlists\Requests\PlaylistStoreRequest;
use App\Api\Playlists\Requests\PlaylistUpdateRequest;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class VideoPlaylistController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
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
            'title' => (string) $playlist->type->label(),
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
            'title' => (string) $playlist->type->label(),
            'description' => __('The playlist has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}
