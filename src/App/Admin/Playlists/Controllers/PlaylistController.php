<?php

declare(strict_types=1);

namespace App\Admin\Playlist\Controllers;

use App\Api\Playlists\Requests\PlaylistIndexRequest;
use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Playlist\Enums\PlaylistSort;
use Domain\Playlists\Enums\PlaylistOrder;
use Domain\Playlists\Models\Playlist;
use Foundation\Http\Controllers\Controller;
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
            new Middleware('verified'),
            new Middleware('precognitive'),
        ];
    }

    public function index(PlaylistIndexRequest $request): Response
    {
        Gate::authorize('viewAny', Playlist::class);

        // Apply filters
        $search = $request->safe()->input('search', '');
        $sort = $request->safe()->input('sort', PlaylistSort::Newest);

        // Scout builder
        $scout = Playlist::search($search)
            ->simplePaginate(16)
            ->through(fn (Playlist $playlist) => new PlaylistResource($playlist));

        return Inertia::render('Admin/Playlist/PlaylistIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'sort' => fn () => $sort,
            'sorters' => fn () => PlaylistSort::options(),
        ]);
    }
}
