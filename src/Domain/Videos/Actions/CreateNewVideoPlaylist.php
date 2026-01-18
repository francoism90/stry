<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Models\Media;
use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Shaka\Facades\Shaka;
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

            // Check if encryption is enabled
            $useEncryption = Playlist::getEncryptionMethod() === 'raw_key_encryption';

            /** @var Playlist $playlist */
            $playlist = $video->createPlaylist([
                'type' => 'clip',
            ]);

            // Enable AES encryption with key rotation if configured
            if ($useEncryption) {
                $useKeyRotation = Playlist::getKeyRotationEnabled();

                $keyData = $opener->withAESEncryption();

                if (! $useKeyRotation) {
                    $playlist->updateOrFail([
                        'encryption_key_id' => $keyData['key_id'],
                        'encryption_key' => $keyData['key'],
                    ]);
                }

                if ($useKeyRotation) {
                    $opener->withKeyRotationDuration(Playlist::getKeyRotationDuration());
                }
            }

            // Iterate through each clip and add to the playlist
            $clips->each(function (Media $media) use ($opener, $useEncryption) {
                // Get the path relative to the disk root
                $path = $media->getPathRelativeToRoot();

                // Use TS segments for AES-128-CBC encryption, fMP4 otherwise
                $videoExtension = $useEncryption ? 'ts' : 'mp4';
                $audioExtension = $useEncryption ? 'ts' : 'mp4';

                // Add video and audio streams for the clip
                $opener
                    ->addVideoStream($path, "{$media->uuid}_video.{$videoExtension}")
                    ->addAudioStream($path, "{$media->uuid}_audio.{$audioExtension}");
            });

            // Configure HLS playlist settings
            $opener
                ->withHlsMasterPlaylist($playlist->getFileName())
                ->withSegmentDuration(Playlist::getSegmentDuration())
                ->withOption('transport_stream_timestamp_offset_ms', 1000);

            // Add text tracks (captions) to the playlist if available
            $video->getCaptions()->each(fn (Media $caption) => $opener->addTextStream($caption->getPath(), $caption->file_name, [
                'language' => $caption->getCustomProperty('language_code', 'en'),
            ]));

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
