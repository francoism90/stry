<?php

declare(strict_types=1);

namespace App\Modules\Playlists\Controllers;

use App\Modules\Playlists\Requests\PlaylistUpdateRequest;
use Domain\Playlists\Models\Playlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PlaylistController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function update(Playlist $playlist, PlaylistUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $playlist);

        // Update playlist details
        $playlist->updateOrFail($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $playlist->getRouteKey(),
            'description' => __('The playlist has been updated.'),
            'type' => 'success',
        ]);

        return back();
    }

    public function destroy(Playlist $playlist): RedirectResponse
    {
        Gate::authorize('delete', $playlist);

        // Delete the playlist
        $playlist->deleteOrFail();

        // Notify the user
        Inertia::flash([
            'title' => (string) $playlist->getRouteKey(),
            'description' => __('The playlist has been deleted.'),
            'type' => 'warning',
        ]);

        return back();
    }
}
