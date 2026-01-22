<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Models\Media;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Shaka\Facades\Shaka;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CreateNewVideoPlaylist
{
    public function handle(Video $video, Closure $next): mixed
    {
        // Skip if there are no clips associated with the video
        if (! $video->hasMedia('clips')) {
            return $next($video);
        }

        // Get the collection of clips for the video
        $clips = $video->getClipCollection();

        // Get the path relative to the disk root
        $paths = $clips->map(fn (Media $clip) => $clip->getPathRelativeToRoot());

        // Initialize Shaka Packager
        $opener = Shaka::fromDisk($clips->first()->disk)->open($paths->toArray());

        /** @var Playlist $playlist */
        $playlist = $video->createPlaylist([
            'type' => 'clip',
        ]);

        // Check if encryption is enabled
        $useEncryption = Playlist::getEncryptionMethod() === 'raw_key_encryption';

        // Enable AES encryption with key rotation if configured
        if ($useEncryption) {
            // Get the protection scheme
            $protectionScheme = Playlist::getProtectionScheme();

            logger($protectionScheme);

            $opener->withAESEncryption('key', $protectionScheme);

            if (Playlist::getKeyRotation()) {
                $opener->withKeyRotationDuration(Playlist::getKeyRotationDuration());
            }
        }

        // Iterate through each clip and add to the playlist
        $clips->each(function (Media $media) use ($opener) {
            // Get the path relative to the disk root
            $path = $media->getPathRelativeToRoot();

            // Detect available streams using FFMpeg
            $ffprobe = FFMpeg::fromDisk($media->disk)->open($path);

            // Use fMP4 (CMAF) segments for better codec support (AV1, HEVC, etc.)
            // m4s extension works with both encrypted and unencrypted content
            if ($ffprobe->getVideoStream()) {
                $opener->addVideoStream($path, "{$media->uuid}_video.\$Number\$.m4s");
            }

            if ($ffprobe->getAudioStream()) {
                $opener->addAudioStream($path, "{$media->uuid}_audio.\$Number\$.m4s");
            }
        });

        // Configure HLS playlist settings
        $opener
            ->withHlsMasterPlaylist($playlist->getFileName())
            ->withSegmentDuration(Playlist::getSegmentDuration())
            ->withOption('hls_playlist_type', 'VOD')
            ->withOptions(Playlist::getPackagerOptions());

        // Add text tracks (captions) to the playlist if available
        if ($video->getCaptions()->isNotEmpty()) {
            $video->getCaptions()->each(fn (Media $caption) => $opener->addTextStream($caption->getPath(), $caption->file_name, [
                'language' => $caption->getCustomProperty('language_code', 'en'),
            ]));
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
    }
}
