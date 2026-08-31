<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Playlists\DataObjects\CaptionStream;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Videos\Models\Video;
use Foxws\Streamer\Facades\Streamer;
use Foxws\Streamer\Support\VideoResolution;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Throwable;

class CreateNewVideoStream
{
    public function __construct(
        protected PlaylistSettings $settings,
    ) {}

    public function handle(Video $video): Collection
    {
        // Get the playlist type from the configuration
        $type = PlaylistType::Streamer;

        $settings = $this->settings;

        // Skip if there are no clips associated with the video
        if ($video->hasPlaylist($type) || ! $video->hasMedia('clips')) {
            return Collection::empty();
        }

        // Get the collection of clips for the video, grouped by disk
        $clips = $video->getClips()->groupBy('disk');

        // Get the collection of captions for the video (if any)
        $captions = $video->getCaptions()->map(fn (Media $caption) => CaptionStream::from([
            'id' => $caption->getKey(),
            'disk' => $caption->disk,
            'path' => $caption->getPath(),
            'language' => $caption->getCustomProperty('language_code', 'en'),
        ]));

        return $clips->map(function (MediaCollection $mediaCollection, string $disk) use ($video, $captions, $settings, $type) {
            // Get all the paths for the media in this collection
            $paths = $mediaCollection->map(fn (Media $media) => $media->getPathRelativeToRoot());

            // Initialize Streamer
            $streamer = Streamer::fromDisk($disk)->open($paths->toArray());

            // Use system binaries
            $streamer->useSystemBinaries();

            // Initialize an array to keep track of added resolutions for the playlist
            $resolutions = [];

            // Iterate through each clip and add to the playlist
            $mediaCollection->each(function (Media $media, int $index) use ($streamer, &$resolutions) {
                // Get the path relative to the disk root
                $path = $media->getPathRelativeToRoot();

                // Detect available streams using FFMpeg
                $ffprobe = FFMpeg::fromDisk($media->disk)->open($path);

                // Add streams only if they exist
                if ($videoStream = $ffprobe->getVideoStream()) {
                    $streamer->addVideoStream($path, "{$index}_video.mp4");

                    // Find the highest supported resolution for the video stream
                    $resolution = VideoResolution::make(
                        $videoStream->getDimensions()->getHeight()
                    )->last();

                    if ($resolution && ! in_array($resolution, $resolutions, strict: true)) {
                        $resolutions[] = $resolution;
                    }
                }

                if ($ffprobe->getAudioStream()) {
                    $streamer->addAudioStream($path, "{$index}_audio.mp4");
                }
            });

            // Add available resolutions (if any)
            if (filled($resolutions)) {
                $streamer->withResolutions($resolutions);
            }

            // Add text streams for captions if they exist. Fragmented into MP4 (rather than a raw
            // .vtt sidecar) so shaka-packager can emit a real segment index — a bare .vtt output
            // gets a <SegmentBase> with no @indexRange, which Shaka Player silently drops.
            $captions->each(fn (CaptionStream $caption) => $streamer->addTextStream($caption->path, "{$caption->id}_caption.mp4", [
                'language' => $caption->language,
            ]));

            // Enable AES encryption with key rotation if configured
            $encryptionKey = null;

            if (filled($settings->encryption)) {
                $encryptionKey = $streamer->withAESEncryption('key', $settings->protection_scheme?->value);

                if ($settings->key_rotation) {
                    $streamer->withKeyRotationDuration($settings->key_rotation_duration);
                }
            }

            /** @var Playlist $playlist */
            $playlist = $video->createPlaylist([
                'encryption_key_id' => $encryptionKey?->keyId,
                'encryption_key' => $encryptionKey?->key,
                'type' => $type,
            ]);

            // Configure DASH playlist settings
            $streamer
                ->withMpdOutput($playlist->getFileName())
                ->withStreamingMode('vod')
                ->withSegmentPerFile();

            try {
                $streamer
                    ->export()
                    ->toDisk($playlist->getDisk())
                    ->toPath($playlist->getPath())
                    ->afterSaving(fn () => $playlist->markAsReady())
                    ->save();
            } catch (Throwable $exception) {
                // If an error occurs during packaging, mark the playlist as failed and rethrow the exception
                $playlist->markAsFailed();

                throw $exception;
            } finally {
                $streamer->cleanupTemporaryFiles();
            }

            return $playlist;
        });
    }
}
