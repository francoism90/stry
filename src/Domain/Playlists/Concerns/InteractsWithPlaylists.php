<?php

declare(strict_types=1);

namespace Domain\Playlists\Concerns;

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\Settings\PlaylistSettings;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait InteractsWithPlaylists
{
    public static function bootInteractsWithPlaylists(): void
    {
        static::deleting(function (self $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model)) && ! $model->isForceDeleting()) {
                return;
            }

            $model->playlists()->cursor()->each(fn (Playlist $playlist) => $playlist->delete());
        });
    }

    /**
     * @return MorphMany<Playlist, $this>
     */
    public function playlists(): MorphMany
    {
        return $this->morphMany(Playlist::class, 'playlistable')->chaperone();
    }

    public function createPlaylist(array $attributes = []): Playlist
    {
        $settings = app(PlaylistSettings::class);

        return $this->playlists()->create([
            'file_name' => 'index.mpd',
            'dash_file_name' => 'index.mpd',
            'hls_file_name' => 'master.m3u8',
            'disk' => $settings->disk_name,
            'type' => $settings->type,
            'expires_at' => $settings->expires_after === 0 ? null : now()->addSeconds($settings->expires_after),
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
            ->current()
            ->exists();
    }
}
