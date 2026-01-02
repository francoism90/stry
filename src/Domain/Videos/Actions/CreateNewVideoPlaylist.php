<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Models\Media;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Shaka\Facades\Shaka;
use Foxws\Shaka\Support\EncryptionKeyGenerator;
use Illuminate\Support\Facades\DB;

class CreateNewVideoPlaylist
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            // Skip if there are no clips associated with the video
            if (! $video->hasMedia('clips')) {
                return $next($video);
            }

            // Get the media items from the video
            $clips = $video->getClipCollection();

            // Get the path relative to the disk root
            $paths = $clips->map(fn (Media $clip) => $clip->getPathRelativeToRoot());

            // Initialize Shaka Packager
            $opener = Shaka::fromDisk($clips->first()->disk)->open($paths->toArray());

            // Get encryption method from config
            $encryptionMethod = Playlist::getEncryptionMethod();

            // Generate encryption keys if enabled
            $encryption = $encryptionMethod === 'raw_key_encryption'
                ? EncryptionKeyGenerator::generate()
                : null;

            /** @var Playlist $playlist */
            $playlist = $video->createPlaylist([
                'encryption_key_id' => $encryption['key_id'] ?? null,
                'encryption_key' => $encryption['key'] ?? null,
                'type' => 'clip',
            ]);

            // Iterate through each clip and add to the playlist
            $paths->each(function (string $path) use ($opener, $encryption) {
                // Create HLS playlist - use TS segments for AES-128-CBC encryption compatibility
                // fMP4 + cbc1 may not be supported; TS segments work reliably with AES-128
                $videoExtension = $encryption ? 'ts' : 'mp4';
                $audioExtension = $encryption ? 'ts' : 'mp4';

                // Add video and audio streams for the clip
                $opener
                    ->addVideoStream($path, "{$media->uuid}_video.{$videoExtension}")
                    ->addAudioStream($path, "{$media->uuid}_audio.{$audioExtension}");
            });

            // Configure HLS playlist settings
            $opener
                ->withHlsMasterPlaylist($playlist->getFileName())
                ->withSegmentDuration(Playlist::getSegmentDuration());

            // Add text tracks (captions) to the playlist if available
            $video->getCaptions()->each(fn (Media $caption) => $opener->addTextStream($caption->getPath(), $caption->file_name, [
                'language' => $caption->getCustomProperty('language_code', 'en'),
            ]));

            // Add AES-128-CBC encryption for browser compatibility
            if ($encryption) {
                // Store the encryption key file in the secrets disk
                $keyPath = $playlist->getPath('encryption.key');

                EncryptionKeyGenerator::writeKeyFile($playlist->getSecretDisk(), $keyPath, $encryption['key']);

                // Note: hls_key_uri is not validated, so we merge it with encryption config
                // The DynamicHLSPlaylist middleware will replace 'encryption.key' with the signed URL
                $opener->withEncryption([
                    'protection_scheme' => 'cbc1', // AES-128-CBC for browser compatibility
                    'hls_key_uri' => 'encryption.key', // Placeholder URI that will be resolved dynamically
                    'clear_lead' => 0, // Encrypt all segments including the first one (default is 5 seconds)
                    'keys' => EncryptionKeyGenerator::formatForShaka(
                        $encryption['key_id'],
                        $encryption['key']
                    ),
                ]);
            }

            // Export the playlist to the configured disk and path
            $opener
                ->export()
                ->toDisk($playlist->getDisk())
                ->toPath($playlist->getPath())
                ->save();

            // Mark the playlist as ready
            $playlist->markAsReady();

            // Cleanup temporary files
            $opener->cleanupTemporaryFiles();

            return $next($video);
        });
    }
}
