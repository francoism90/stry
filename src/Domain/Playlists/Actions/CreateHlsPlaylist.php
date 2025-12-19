<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use Foxws\Shaka\Facades\Shaka;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\X264;

class CreateHlsPlaylist
{
    public function handle(string $disk, string $path)
    {
        return DB::transaction(function () use ($disk, $path) {
            $shaka = Shaka::fromDisk($disk)
                ->open($path)
                ->export()
                ->addVideoStream($path, 'video.mp4')
                ->addAudioStream($path, 'audio.mp4')
                ->withHlsMasterPlaylist('master.m3u8')
                ->toDisk('export')
                ->save();

            // Use rotation key if specified
            // if (Playlist::shouldUseRotationKeys()) {
            //     $secrets = $playlist->getSecretFilesystem();
            //     $segmentsPerKey = Playlist::getRotationKeysSections();

            //     $ffmpeg->withRotatingEncryptionKey(fn (string $filename, string $contents) => $secrets->put($playlist->getPath($filename), $contents), $segmentsPerKey);
            // }


            dd($shaka);


            return $playlist;
        });
    }
}
