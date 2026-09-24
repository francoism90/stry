<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Playlists\DataObjects\CaptionStream;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Videos\Concerns\CreatesVideoPlaylists;
use Domain\Videos\Models\Video;
use Foxws\Streamer\Facades\Streamer;
use Foxws\Streamer\Support\VideoResolution;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

class CreateNewVideoStream
{
    use CreatesVideoPlaylists;

    public function __construct(
        protected PlaylistSettings $settings,
    ) {}

    public function handle(Video $video): Collection
    {
        // Get the playlist type from the configuration
        $type = PlaylistType::Streamer;

        $settings = $this->settings;

        // Skip if the playlist already exists or there are no clips associated with the video
        if (! $this->shouldCreatePlaylist($video, $type)) {
            return Collection::empty();
        }

        // Get the collection of clips for the video, grouped by disk
        $clips = $video->getClips()->groupBy('disk');

        // Get the collection of captions for the video (if any)
        $captions = $this->getCaptionStreams($video);

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

            // Enable AES encryption if configured. Key rotation is skipped: Shaka Streamer doesn't support it.
            $encryptionKey = null;

            if (filled($settings->encryption)) {
                $encryptionKey = $streamer->withAESEncryption('key', $settings->protection_scheme?->value);
            }

            $playlist = $this->createPlaylist($video, $type, $encryptionKey);

            // Configure DASH and HLS playlist settings. Shaka Streamer builds both
            // manifests from the same CMAF-packaged streams in one pipeline run.
            $streamer
                ->withMpdOutput($playlist->getDashFileName() ?? 'index.mpd')
                ->withHlsMasterPlaylist($playlist->getHlsFileName() ?? 'master.m3u8')
                ->withStreamingMode('vod')
                ->withSegmentPerFile();

            // Export the playlist to the configured disk and path
            $this->exportPlaylist($streamer, $playlist);

            return $playlist;
        });
    }
}
