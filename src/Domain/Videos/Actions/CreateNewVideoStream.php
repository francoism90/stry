<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Streamer\Facades\Streamer;
use Foxws\Streamer\Support\VideoResolution;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Throwable;

class CreateNewVideoStream
{
    public function handle(Video $video): void
    {
        // Skip if there are no clips associated with the video
        if ($video->hasPlaylist() || ! $video->hasMedia('clips')) {
            return;
        }

        // Get the collection of clips for the video
        $clips = $video->getClips();

        // Get the path relative to the disk root
        $paths = $clips->map(fn (Media $clip) => $clip->getPathRelativeToRoot());

        // Initialize Streamer
        $streamer = Streamer::fromDisk($clips->first()->disk)->open($paths->toArray());

        // Use system binaries
        $streamer->useSystemBinaries();

        // Get encryption configuration
        $encryptionMethod = Playlist::getEncryptionMethod();
        $protectionScheme = Playlist::getProtectionScheme();

        // Check if we use encryption
        $useEncryption = filled($encryptionMethod);

        // Enable AES encryption with key rotation if configured
        if ($useEncryption) {
            $keyData = $streamer->withAESEncryption('key', $protectionScheme);

            if (Playlist::getKeyRotation()) {
                $streamer->withKeyRotationDuration(Playlist::getKeyRotationDuration());
            }
        }

        // Initialize an array to keep track of added resolutions for the playlist
        $resolutions = [];

        // Iterate through each clip and add to the playlist
        $clips->each(function (Media $media) use ($streamer, $resolutions, $useEncryption, $protectionScheme) {
            // Get the path relative to the disk root
            $path = $media->getPathRelativeToRoot();

            // Detect available streams using FFMpeg
            $ffprobe = FFMpeg::fromDisk($media->disk)->open($path);

            // Use TS segments for SAMPLE-AES encryption (protection_scheme = null)
            // Use m4s segments for CENC/CBCS encryption (protection_scheme = 'cenc'/'cbcs')
            $extension = $useEncryption && $protectionScheme === null ? 'ts' : 'm4s';

            // Add streams only if they exist
            if ($videoStream = $ffprobe->getVideoStream()) {
                // Add video stream with the appropriate output filename pattern
                $streamer->addVideoStream($path, "{$media->getKey()}_video.\$Number\$.{$extension}");

                // Find the highest supported resolution for the video stream
                $resolution = VideoResolution::make($videoStream->getDimensions()->getHeight())->first();

                if ($resolution && ! in_array($resolution, $resolutions, strict: true)) {
                    $resolutions[] = $resolution;
                }
            }

            if ($ffprobe->getAudioStream()) {
                $streamer->addAudioStream($path, "{$media->getKey()}_audio.\$Number\$.{$extension}");
            }
        });

        // Add text tracks (captions) to the playlist if available
        if ($video->getCaptions()->isNotEmpty()) {
            $video->getCaptions()->each(fn (Media $caption) => $streamer->addTextStream($caption->getPath(), $caption->file_name, [
                'language' => $caption->getCustomProperty('language_code', 'en'),
            ]));
        }

        // Add available resolutions (if any)
        if (filled($resolutions)) {
            $streamer->withResolutions($resolutions);
        }

        /** @var Playlist $playlist */
        $playlist = $video->createPlaylist([
            'encryption_key_id' => $keyData['key_id'] ?? null,
            'encryption_key' => $keyData['key'] ?? null,
            'type' => PlaylistType::Streamer,
        ]);

        // Configure DASH playlist settings
        $streamer
            ->withMpdOutput($playlist->getFileName())
            ->withStreamingMode('vod')
            ->withSegmentPerFile();

        // Export the playlist to the configured disk and path
        try {
            // Prepare the exporter
            $exporter = $streamer
                ->export()
                ->toDisk($playlist->getDisk())
                ->toPath($playlist->getPath());

            // Save the exported playlist
            $exporter->save();

            // Mark the playlist as ready
            $playlist->markAsReady();
        } catch (Throwable $exception) {
            // Mark the playlist as failed
            $playlist->markAsFailed();

            throw $exception;
        } finally {
            // Clean up temporary files
            $streamer->cleanupTemporaryFiles();
        }
    }
}
