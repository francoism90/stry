<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Models\Video;
use Foxws\Shaka\Facades\Shaka;
use Illuminate\Support\Facades\DB;

class GenerateVideoPlaylist
{
    public function handle(Video $video): Video
    {
        return DB::transaction(function () use ($video) {
            // If the video already has playlists or has no clips, skip processing
            if ($video->hasPlaylist('clip') || ! $video->hasMedia('clips')) {
                return;
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            $path = $media->getPathRelativeToRoot();

            // Create HLS playlist for the clip media
            $shaka = Shaka::fromDisk($media->disk)
                ->open($path)
                ->export()
                ->addVideoStream($path, 'video.mp4')
                ->addAudioStream($path, 'audio.mp4')
                ->withHlsMasterPlaylist('master.m3u8')
                ->toDisk('export')
                ->save();

            // Create the playlist record in the database
            $video->playlists()->create([
                'type' => 'clip',
                'path' => 'master.m3u8',
                'disk' => 'export',
            ]);


            dd($shaka);
        });
    }
}
