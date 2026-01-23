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
        $packager = Shaka::fromDisk($clips->first()->disk)->open($paths->toArray());

        /** @var Playlist $playlist */
        $playlist = $video->createPlaylist([
            'type' => 'clip',
        ]);

        // Get encryption configuration
        $encryptionMethod = Playlist::getEncryptionMethod();
        $protectionScheme = Playlist::getProtectionScheme();

        // Check if we use encryption
        $useEncryption = filled($encryptionMethod);

        // Enable AES encryption with key rotation if configured
        if ($useEncryption) {
            $packager->withAESEncryption('key', $protectionScheme);

            if (Playlist::getKeyRotation()) {
                $packager->withKeyRotationDuration(Playlist::getKeyRotationDuration());
            }
        }

        // Iterate through each clip and add to the playlist
        $clips->each(function (Media $media) use ($packager, $useEncryption, $protectionScheme) {
            // Get the path relative to the disk root
            $path = $media->getPathRelativeToRoot();

            // Detect available streams using FFMpeg
            $ffprobe = FFMpeg::fromDisk($media->disk)->open($path);

            // Use TS segments for SAMPLE-AES encryption (protection_scheme = null)
            // Use m4s segments for CENC/CBCS encryption (protection_scheme = 'cenc'/'cbcs')
            $extension = $useEncryption && $protectionScheme === null ? 'ts' : 'm4s';

            // Add streams only if they exist
            if ($ffprobe->getVideoStream()) {
                $packager->addVideoStream($path, "{$media->getKey()}_video.\$Number\$.{$extension}");
            }

            if ($ffprobe->getAudioStream()) {
                $packager->addAudioStream($path, "{$media->getKey()}_audio.\$Number\$.{$extension}");
            }
        });

        // Configure DASH/MPD playlist settings
        $packager
            ->withMpdOutput($playlist->getFileName())
            ->withSegmentDuration(Playlist::getSegmentDuration())
            ->withOptions(Playlist::getPackagerOptions());

        // Add text tracks (captions) to the playlist if available
        if ($video->getCaptions()->isNotEmpty()) {
            $video->getCaptions()->each(fn (Media $caption) => $packager->addTextStream($caption->getPath(), $caption->file_name, [
                'language' => $caption->getCustomProperty('language_code', 'en'),
            ]));
        }

        // Export the playlist to the configured disk and path
        $packager
            ->export()
            ->toPath($playlist->getPath())
            ->toDisk($playlist->getDisk())
            ->save();

        // Mark the playlist as ready
        $playlist->markAsReady();

        // Cleanup temporary files
        $packager->cleanupTemporaryFiles();

        return $next($video);
    }
}
