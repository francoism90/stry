<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Media\Actions\ExtractMediaCaptions;
use Domain\Transcodes\Models\Transcode;
use Domain\Videos\Models\Video;

class ExtractVideoCaptions
{
    public function handle(Video $video, Closure $next): mixed
    {
        // If the video already has captions or has no clips, skip processing
        if ($video->hasMedia('captions') || ! $video->hasMedia('clips')) {
            return $next($video);
        }

        // Get the first media item from the video
        $media = $video->getClips()->first();

        // Extract captions from the media
        $conversion = app(ExtractMediaCaptions::class)->handle($media);

        // Add the caption media to the video
        $conversion->each(fn (string $path) => $video
            ->addMediaFromDisk($path, Transcode::getDestinationDisk())
            ->toMediaCollection('captions')
            ->saveOrFail(),
        );

        return $next($video);
    }
}
