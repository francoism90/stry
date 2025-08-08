<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateNewPlaylist
{
    public function handle(Model $model, string $disk, string $path, array $attributes = []): Playlist
    {
        return DB::transaction(function () use ($model, $disk, $path, $attributes) {
            // Create a new playlist with the provided attributes
            $playlist = $model->playlists()->create([
                'file_name' => 'index.m3u8',
                'disk' => Playlist::getTranscodeDisk(),
                'secret_disk' => Playlist::getRotationKeyDisk(),
                'expires_at' => Playlist::getExpiresAfter(),
                'accessed_at' => now(),
                ...$attributes]);

            // Create the HLS playlist
            app(CreateHlsPlaylist::class)->handle($playlist, $disk, $path);

            return $playlist;
        });
    }
}
