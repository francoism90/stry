<?php

declare(strict_types=1);

namespace App\Admin\Playlists\Controllers;

use App\Api\Playlists\Requests\PlaylistIndexRequest;
use App\Api\Playlists\Resources\PlaylistResource;
use Domain\Playlists\Enums\PlaylistSort;
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
        $sort = $request->safe()->input('sort', PlaylistSort::Newest);

        // Scout builder
        $scout = Playlist::query()
            ->simplePaginate(16)
            ->through(fn (Playlist $playlist) => new PlaylistResource($playlist));

        return Inertia::render('Admin/Playlists/PlaylistIndex', [
            'items' => Inertia::scroll(fn () => $scout),
            'sort' => fn () => $sort,
            'sorters' => fn () => PlaylistSort::options(),
        ]);
    }
}
