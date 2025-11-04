<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Actions\ExtractMediaCaptions;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class ExtractVideoCaptions
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            // If the video already has captions or has no clips, skip processing
            if ($video->hasMedia('captions') || ! $video->hasMedia('clips')) {
                return $next($video);
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Extract captions from the media
            $conversion = app(ExtractMediaCaptions::class)->handle($media);

            // Add the caption media to the video
            $conversion->each(fn (string $path) => $video
                ->addMediaFromDisk($path, 'transcodes')
                ->toMediaCollection('captions')
                ->saveOrFail(),
            );

            return $next($video);
        });
    }
}
