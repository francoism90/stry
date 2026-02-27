<?php

declare(strict_types=1);

namespace Domain\Playlists\Concerns;

use Domain\Playlists\Enums\PlaylistType;
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
            'disk' => Playlist::getDestinationDisk(),
            'type' => Playlist::getDefaultType(),
            'expires_at' => Playlist::getExpiresAfter(),
            ...$attributes,
        ]);
    }

    public function getPlaylist(?PlaylistType $type = null): ?Playlist
    {
        return $this->playlists()
            ->type($type)
            ->ordered()
            ->first();
    }

    public function hasPlaylist(?PlaylistType $type = null): bool
    {
        return $this->playlists()
            ->type($type)
            ->active()
            ->exists();
    }
}
