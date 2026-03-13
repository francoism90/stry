<?php

declare(strict_types=1);

namespace App\Web\Playlists\Controllers;

use App\Api\Playlists\Requests\PlaylistIndexRequest;
use App\Api\Playlists\Requests\PlaylistUpdateRequest;
use App\Api\Playlists\Resources\PlaylistResource;
use App\Web\Playlists\Responses\PlaylistResourceProperty;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\Scopes\PlaylistFilterScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PlaylistController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('precognitive'),
        ];
    }

    public function index(PlaylistIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Playlist::class);

        // Apply filters
        $type = $request->safe()->input('type');

        // Query builder
        $query = Playlist::query()
            ->tap(new PlaylistFilterScope(type: $type))
            ->simplePaginate(16);

        return Inertia::render('Admin/Playlists/PlaylistIndex', [
            'items' => Inertia::scroll(fn () => PlaylistResource::collection($query)),
            'type' => fn () => $type,
            'types' => fn () => PlaylistType::options(),
        ]);
    }

    public function edit(Playlist $playlist): Response
    {
        Gate::authorize('update', $playlist);

        return Inertia::render('Admin/Playlists/PlaylistEdit', [
            'playlist' => fn () => new PlaylistResourceProperty($playlist),
            'types' => fn () => PlaylistType::options(),
        ]);
    }

    public function update(Playlist $playlist, PlaylistUpdateRequest $request): RedirectResponse
    {
        Gate::authorize('update', $playlist);

        // Update playlist details
        $playlist->updateOrFail($request->safe()->all());

        // Notify the user
        Inertia::flash([
            'title' => (string) $playlist->file_name,
            'description' => __('The playlist has been updated.'),
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
            'title' => (string) $playlist->file_name,
            'description' => __('The playlist has been deleted.'),
        ]);

        return back();
    }
}
