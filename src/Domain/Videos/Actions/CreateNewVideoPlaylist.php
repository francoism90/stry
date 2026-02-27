<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Playlists\DataObjects\CaptionStream;
use Domain\Playlists\DataObjects\PlaylistSettings;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Shaka\Facades\Shaka;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Throwable;

class CreateNewVideoPlaylist
{
    public function handle(Video $video): Collection
    {
        // Get the playlist settings from the configuration
        $settings = PlaylistSettings::from(PlaylistType::Streamer);

        // Skip if there are no clips associated with the video
        if ($video->hasPlaylist($settings->type) || ! $video->hasMedia('clips')) {
            return Collection::empty();
        }

        // Get the collection of clips for the video, grouped by disk
        $clips = $video->getClips()->groupBy('disk');

        // Get the collection of captions for the video (if any)
        $captions = $video->getCaptions()->map(fn (Media $caption) => CaptionStream::from([
            'disk' => $caption->disk,
            'path' => $caption->getPathRelativeToRoot(),
            'language' => $caption->getCustomProperty('language_code', 'en'),
        ]));

        return $clips->map(function (MediaCollection $mediaCollection, string $disk) use ($video, $captions, $settings) {
            // Get all the paths for the media in this collection
            $paths = $mediaCollection->map(fn (Media $media) => $media->getPathRelativeToRoot());

            // Initialize Shaka Packager
            $packager = Shaka::fromDisk($disk)->open($paths->toArray());

            // Add video and audio streams to the packager based on the available streams in each clip
            $paths->each(function (string $path, int $index) use ($packager, $disk) {
                // Detect available streams using FFMpeg
                $ffprobe = FFMpeg::fromDisk($disk)->open($path);

                if ($ffprobe->getVideoStream()) {
                    $packager->addVideoStream($path, "{$index}_video.mp4");
                }

                if ($ffprobe->getAudioStream()) {
                    $packager->addAudioStream($path, "{$index}_audio.mp4");
                }
            });

            // Add text streams for captions if they exist
            $captions->each(fn (CaptionStream $caption) => $packager->addTextStream($caption->path, $caption->disk, [
                'language' => $caption->language,
            ]));

            // Enable AES encryption with key rotation if configured
            if ($settings->encryption) {
                $keyData = $packager->withAESEncryption('key', $settings->protectionScheme);

                if ($settings->keyRotation) {
                    $packager->withKeyRotationDuration($settings->keyRotationDuration);
                }
            }

            /** @var Playlist $playlist */
            $playlist = $video->createPlaylist([
                'file_name' => 'index.mpd',
                'encryption_key_id' => $keyData['key_id'] ?? null,
                'encryption_key' => $keyData['key'] ?? null,
                'type' => $settings->type,
            ]);

            // Configure the packager with common settings
            $packager
                ->withMpdOutput($playlist->file_name)
                ->withAllowCodecSwitching()
                ->withSegmentDuration($settings->segmentDuration)
                ->withFragmentDuration($settings->fragmentDuration);

            // Export the playlist to the configured disk and path
            try {
                $packager
                    ->export()
                    ->toDisk($playlist->getDisk())
                    ->toPath($playlist->getPath())
                    ->afterSaving(fn () => $playlist->markAsCompleted())
                    ->save();
            } catch (Throwable $exception) {
                // If an error occurs during packaging, mark the playlist as failed and rethrow the exception
                $playlist->markAsFailed();

                throw $exception;
            } finally {
                // Clean up any temporary files created during packaging
                $packager->cleanupTemporaryFiles();
            }

            return $playlist;
        });
    }
}
