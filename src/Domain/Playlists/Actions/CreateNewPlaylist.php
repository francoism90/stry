<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateNewPlaylist
{
    public function handle(Model $model, array $attributes = []): Playlist
    {
        return DB::transaction(function () use ($model, $attributes) {
            return $model->playlists()->create([
                'file_name' => 'index.m3u8',
                'disk' => Playlist::getTranscodeDisk(),
                'secret_disk' => Playlist::getRotationKeyDisk(),
                'expires_at' => Playlist::getExpiresAfter(),
            ], ...$attributes);
        });
    }
}
