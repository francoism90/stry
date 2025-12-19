<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Shaka\Facades\Shaka;
use Foxws\Shaka\Support\EncryptionKeyGenerator;
use Illuminate\Support\Facades\DB;

class CreateNewVideoPlaylist
{
    public function handle(Video $video): ?Playlist
    {
        return DB::transaction(function () use ($video) {
            // If the video already has playlists or has no clips, skip processing
            if ($video->hasPlaylist('clip') || ! $video->hasMedia('clips')) {
                return;
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            $path = $media->getPathRelativeToRoot();

            // Generate encryption keys for HLS
            $encryption = EncryptionKeyGenerator::generate();

            /** @var Playlist $playlist */
            $playlist = $video->playlists()->create([
                'file_name' => 'master.m3u8',
                'type' => 'clip',
                'disk' => Playlist::getDestinationDisk(),
                'encryption_key_id' => $encryption['key_id'],
                'encryption_key' => $encryption['key'],
            ]);

            // Store the encryption key file in the secrets disk
            $secretDisk = Playlist::getRotationKeyDisk();

            $keyPath = $playlist->getPath('encryption.key');

            EncryptionKeyGenerator::writeKeyFile($secretDisk, $keyPath, $encryption['key']);

            // Create HLS playlist for the clip media with custom UUID path and encryption
            $packager = Shaka::fromDisk($media->disk)
                ->open($path)
                ->export()
                ->toDisk($playlist->getDisk())
                ->outputPath($playlist->getPath())
                ->addVideoStream($path, 'video.mp4')
                ->addAudioStream($path, 'audio.mp4')
                ->withHlsMasterPlaylist('master.m3u8')
                ->withSegmentDuration(Playlist::getSegmentLength())
                ->withEncryption([
                    'keys' => EncryptionKeyGenerator::formatForShaka(
                        $encryption['key_id'],
                        $encryption['key']
                    ),
                    'hls_key_uri' => 'encryption.key',
                ])
                ->save();

            // Clean up temporary files used during packaging
            $packager->cleanupTemporaryFiles();

            // Mark the playlist as ready
            $playlist->markAsReady();

            return $playlist;
        });
    }
}
