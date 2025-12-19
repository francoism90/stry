<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Playlists\Models\Playlist;
use Domain\Videos\Models\Video;
use Foxws\Shaka\Facades\Shaka;
use Illuminate\Support\Facades\DB;

class CreateNewVideoPlaylist
{
    public function handle(Video $video): ?Playlist
    {
        return DB::transaction(function () use ($video) {
            // If the video already has playlists or has no clips, skip processing
            if ($video->hasPlaylist('clip') || ! $video->hasMedia('clips')) {
                return;
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            $path = $media->getPathRelativeToRoot();

            /** @var Playlist $playlist */
            $playlist = $video->playlists()->create([
                'file_name' => 'master.m3u8',
                'type' => 'clip',
                'disk' => Playlist::getDestinationDisk(),
            ]);

            // Create HLS playlist for the clip media with custom UUID path
            $packager = Shaka::fromDisk($media->disk)
                ->open($path)
                ->export()
                ->toDisk($playlist->getDisk())
                ->outputPath($playlist->getPath())
                ->addVideoStream($path, 'video.mp4')
                ->addAudioStream($path, 'audio.mp4')
                ->withHlsMasterPlaylist('master.m3u8')
                ->withSegmentDuration(Playlist::getSegmentLength())
                ->save();

            // Clean up temporary files used during packaging
            $packager->cleanupTemporaryFiles();

            return $playlist;
        });
    }
}
