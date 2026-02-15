<?php

declare(strict_types=1);

namespace Domain\Playlists\Concerns;

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
            'file_name' => 'index.mpd',
            'disk' => Playlist::getDestinationDisk(),
            'expires_at' => Playlist::getExpiresAfter(),
            'accessed_at' => now(),
            ...$attributes,
        ]);
    }

    public function hasPlaylist(): bool
    {
        return $this->playlists()->unexpired()->exists();
    }

    public function getPlaylist(): ?Playlist
    {
        return $this->playlists()->unexpired()->first();
    }
}
