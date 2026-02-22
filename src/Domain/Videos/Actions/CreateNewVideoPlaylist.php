<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Shaka\Facades\Shaka;
use Throwable;

class CreateNewVideoPlaylist
{
    public function handle(Video $video): void
    {
        // Skip if there are no clips associated with the video
        if ($video->hasPlaylist() || ! $video->hasMedia('clips')) {
            return;
        }

        // Get the collection of clips for the video
        $clips = $video->getClipCollection();

        // Get the path relative to the disk root
        $paths = $clips->map(fn (Media $clip) => $clip->getPathRelativeToRoot());

        // Initialize Shaka Packager
        $packager = Shaka::fromDisk($clips->first()->disk)->open($paths->toArray());

        // Get encryption configuration
        $encryptionMethod = Playlist::getEncryptionMethod();
        $protectionScheme = Playlist::getProtectionScheme();

        // Check if we use encryption
        $useEncryption = filled($encryptionMethod);

        // Enable AES encryption with key rotation if configured
        if ($useEncryption) {
            $keyData = $packager->withAESEncryption('key', $protectionScheme);

            if (Playlist::getKeyRotation()) {
                $packager->withKeyRotationDuration(Playlist::getKeyRotationDuration());
            }
        }

        // Iterate through each clip and add to the playlist
        $clips->each(function (Media $media) use ($packager, $useEncryption, $protectionScheme) {
            // Get the path relative to the disk root
            $path = $media->getPathRelativeToRoot();

            // Use TS segments for SAMPLE-AES encryption (protection_scheme = null)
            // Use m4s segments for CENC/CBCS encryption (protection_scheme = 'cenc'/'cbcs')
            $extension = $useEncryption && $protectionScheme === null ? 'ts' : 'm4s';

            // Add streams only if they exist
            if ($media->hasVideoStream()) {
                $packager->addVideoStream($path, "{$media->getKey()}_video.\$Number\$.{$extension}");
            }

            if ($media->hasAudioStream()) {
                $packager->addAudioStream($path, "{$media->getKey()}_audio.\$Number\$.{$extension}");
            }
        });

        /** @var Playlist $playlist */
        $playlist = $video->createPlaylist([
            'encryption_key_id' => $keyData['key_id'] ?? null,
            'encryption_key' => $keyData['key'] ?? null,
            'type' => PlaylistType::Packager,
        ]);

        // Configure DASH playlist settings
        $packager->withMpdOutput($playlist->getFileName());

        // Add text tracks (captions) to the playlist if available
        if ($video->getCaptions()->isNotEmpty()) {
            $video->getCaptions()->each(fn (Media $caption) => $packager->addTextStream($caption->getPath(), $caption->file_name, [
                'language' => $caption->getCustomProperty('language_code', 'en'),
            ]));
        }

        try {
            // Export the playlist to the configured disk and path
            $packager
                ->export()
                ->toDisk($playlist->getDisk())
                ->toPath($playlist->getPath())
                ->save();

            // Mark the playlist as ready
            $playlist->markAsReady();
        } catch (Throwable $exception) {
            // Mark the playlist as failed
            $playlist->markAsFailed();

            throw $exception;
        } finally {
            // Clean up temporary files
            $packager->cleanupTemporaryFiles();
        }
    }
}
