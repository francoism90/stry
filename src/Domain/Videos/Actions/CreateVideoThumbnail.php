<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Actions\CreateMediaFrame;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoThumbnail
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            if ($video->hasMedia('thumbnail') || ! $video->hasMedia('clips')) {
                return $next($video);
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Create a sample video from the segments
            $conversion = app(CreateMediaFrame::class)->handle($media, floatval($video->snapshot ?? 0));

            // Add the sample video to the video model
            $video
                ->addMediaFromDisk($conversion->path('frame.jpg'), 'transcodes')
                ->preservingOriginal()
                ->toMediaCollection('thumbnail')
                ->saveOrFail();

            // Clean up temporary files
            $conversion->delete();

            return $next($video);
        });
    }
}
