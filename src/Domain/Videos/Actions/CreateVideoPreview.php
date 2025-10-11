<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Actions\CreateMediaSegments;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoPreview
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            if ($video->hasMedia('previews') || ! $video->hasMedia('clips')) {
                return $next($video);
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Generate media segments
            $conversion = app(CreateMediaSegments::class)->handle($media);

            // Add the preview video to the video
            $video
                ->addMediaFromDisk($conversion->path('sample.mp4'), 'transcodes')
                ->toMediaCollection('previews')
                ->saveOrFail();

            // Clean up temporary files
            $conversion->delete();

            return $next($video);
        });
    }
}
