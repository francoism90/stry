<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Closure;
use Domain\Playlists\Models\Playlist;
use Illuminate\Support\Facades\DB;

class MarkPlaylistAsAccessed
{
    public function handle(Playlist $playlist, Closure $next): mixed
    {
        return DB::transaction(function () use ($playlist, $next) {
            // Update the accessed_at timestamp
            $playlist->touchQuietly('accessed_at');

            return $next($playlist);
        });
    }
}
