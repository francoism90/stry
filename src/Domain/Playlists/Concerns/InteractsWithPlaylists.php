<?php

declare(strict_types=1);

namespace Domain\Playlists\Concerns;

use ArrayAccess;
use Domain\Playlists\Collections\PlaylistCollection;
use Domain\Playlists\Models\Playlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait InteractsWithPlaylists
{
    public static function bootInteractsWithPlaylists(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->playlists()->cursor()->each(fn (Playlist $playlist) => $playlist->delete());
        });
    }

    public function playlists(): MorphMany
    {
        return $this->morphMany(Playlist::class, 'playlistable')->chaperone();
    }

    public function createPlaylist(array $attributes = []): Playlist
    {
        return $this->playlists()->create([
            'file_name' => 'index.m3u8',
            'disk' => Playlist::getDestinationDisk(),
            'expires_at' => Playlist::getExpiresAfter(),
            'accessed_at' => now(),
            ...$attributes,
        ]);
    }

    public function getPlaylists(ArrayAccess|array|string $type): PlaylistCollection
    {
        return $this
            ->playlists()
            ->type($type)
            ->active()
            ->get();
    }

    public function getFirstPlaylist(ArrayAccess|array|string $type): ?Playlist
    {
        return $this->getPlaylists($type)->first();
    }

    public function hasPlaylist(ArrayAccess|array|string $type): bool
    {
        return $this->getPlaylists($type)->isNotEmpty();
    }
}
