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
        return DB::transaction(function () use ($video): ?Playlist {
            // If the video already has playlists or has no clips, skip processing
            if ($video->hasPlaylist('clip') || ! $video->hasMedia('clips')) {
                return null;
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            $path = $media->getPathRelativeToRoot();

            // Get encryption method from config
            $encryptionMethod = Playlist::getEncryptionMethod();

            // Generate encryption keys if encryption is enabled
            $encryption = $encryptionMethod === 'raw_key_encryption'
                ? EncryptionKeyGenerator::generate()
                : null;

            /** @var Playlist $playlist */
            $playlist = $video->createPlaylist([
                'encryption_key_id' => $encryption['key_id'] ?? null,
                'encryption_key' => $encryption['key'] ?? null,
                'type' => 'clip',
            ]);

            // Create HLS playlist for the clip media with custom UUID path
            // Note: For encrypted HLS, we must use TS segments (not fMP4) for browser compatibility
            // fMP4 with encryption uses SAMPLE-AES-CTR which browsers don't support
            $videoOutput = $encryption ? 'video.ts' : 'video.mp4';
            $audioOutput = $encryption ? 'audio.ts' : 'audio.mp4';

            $packager = Shaka::fromDisk($media->disk)
                ->open($path)
                ->export()
                ->toDisk($playlist->getDisk())
                ->outputPath($playlist->getPath())
                ->addVideoStream($path, $videoOutput)
                ->addAudioStream($path, $audioOutput)
                ->withHlsMasterPlaylist($playlist->getFileName())
                ->withSegmentDuration(Playlist::getSegmentLength());

            // Add encryption if enabled
            if ($encryption) {
                // Store the encryption key file in the secrets disk
                $keyPath = $playlist->getPath('encryption.key');

                EncryptionKeyGenerator::writeKeyFile($playlist->getSecretDisk(), $keyPath, $encryption['key']);

                $packager->withEncryption([
                    'keys' => EncryptionKeyGenerator::formatForShaka(
                        $encryption['key_id'],
                        $encryption['key']
                    ),
                    'hls_key_uri' => 'encryption.key',
                ]);
            }

            $packager->save();

            // Clean up temporary files used during packaging
            // $packager->cleanupTemporaryFiles();

            // Mark the playlist as ready
            $playlist->markAsReady();

            return $playlist;
        });
    }
}
