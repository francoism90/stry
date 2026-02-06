<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Streamer\Facades\Streamer;
use Illuminate\Support\Facades\Log;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Throwable;

class CreateNewVideoPlaylist
{
    public function handle(Video $video): void
    {
        // Skip if there are no clips associated with the video
        if (! $video->hasMedia('clips')) {
            return;
        }

        // Get the collection of clips for the video
        $clips = $video->getClipCollection();

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

        // Iterate through each clip and add to the playlist
        $clips->each(function (Media $media) use ($streamer, $useEncryption, $protectionScheme) {
            // Get the path relative to the disk root
            $path = $media->getPathRelativeToRoot();

            // Detect available streams using FFMpeg
            $ffprobe = FFMpeg::fromDisk($media->disk)->open($path);

            // Use TS segments for SAMPLE-AES encryption (protection_scheme = null)
            // Use m4s segments for CENC/CBCS encryption (protection_scheme = 'cenc'/'cbcs')
            $extension = $useEncryption && $protectionScheme === null ? 'ts' : 'm4s';

            // Add streams only if they exist
            if ($ffprobe->getVideoStream()) {
                $streamer->addVideoStream($path, "{$media->getKey()}_video.\$Number\$.{$extension}");
            }

            if ($ffprobe->getAudioStream()) {
                $streamer->addAudioStream($path, "{$media->getKey()}_audio.\$Number\$.{$extension}");
            }
        });

        /** @var Playlist $playlist */
        $playlist = $video->createPlaylist([
            'encryption_key_id' => $keyData['key_id'] ?? null,
            'encryption_key' => $keyData['key'] ?? null,
            'type' => 'clip',
        ]);

        // Configure DASH playlist settings
        $streamer
            ->withMpdOutput($playlist->getFileName())
            ->withSegmentPerFile()
            ->withAudioCodecs(Playlist::getDefaultAudioCodecs())
            ->withVideoCodecs(Playlist::getDefaultVideoCodecs())
            ->withSegmentDuration(Playlist::getSegmentDuration())
            ->withOptions(Playlist::getStreamerOptions());

        // Add text tracks (captions) to the playlist if available
        if ($video->getCaptions()->isNotEmpty()) {
            $video->getCaptions()->each(fn (Media $caption) => $streamer->addTextStream($caption->getPath(), $caption->file_name, [
                'language' => $caption->getCustomProperty('language_code', 'en'),
            ]));
        }

        // Export the playlist to the configured disk and path
        try {
            $exporter = $streamer
                ->export()
                ->toDisk($playlist->getDisk())
                ->toPath($playlist->getPath());

            $result = $exporter->save();

            // Check if copy operation had failures
            if ($exporter->hasCopyFailures()) {
                $failures = $exporter->getFailedFiles();
                Log::error('Playlist export had file copy failures', [
                    'playlist_id' => $playlist->id,
                    'failures' => $failures,
                    'summary' => $exporter->getCopySummary(),
                ]);

                throw new \RuntimeException(
                    'Failed to copy '.count($failures).' files to storage. '.
                    'See logs for details.'
                );
            }

            // Log successful copy operation
            $summary = $exporter->getCopySummary();
            Log::info('Playlist exported successfully', [
                'playlist_id' => $playlist->id,
                'summary' => $summary,
            ]);

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
