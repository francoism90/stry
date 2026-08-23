<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Media\Actions\GenerateMediaStoryboard;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;

class ExtractVideoStoryboard
{
    public function handle(Video $video, Closure $next): mixed
    {
        // If the video already has a storyboard or has no clips, skip processing
        if ($video->hasMedia('storyboards') || ! $video->hasMedia('clips')) {
            return $next($video);
        }

        // Get the first media item from the video
        $media = $video->getClips()->first();

        // Generate the storyboard sprite and VTT cue file from the media
        $storyboard = app(GenerateMediaStoryboard::class)->handle($media);

        if ($storyboard) {
            $video
                ->addMediaFromDisk($storyboard['image'], Transcode::getDestinationDisk())
                ->toMediaCollection('storyboards');

            $video
                ->addMediaFromDisk($storyboard['vtt'], Transcode::getDestinationDisk())
                ->toMediaCollection('storyboards');
        }

        return $next($video);
    }
}
