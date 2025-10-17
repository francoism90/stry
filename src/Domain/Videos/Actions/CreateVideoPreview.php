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
            // If the video already has a preview or has no clips, skip processing
            if ($video->hasMedia('previews') || ! $video->hasMedia('clips')) {
                return $next($video);
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Create media segments for the preview
            $conversion = app(CreateMediaSegments::class)->handle($media);

            // Add the preview media to the video
            $video
                ->addMediaFromDisk($conversion, 'transcodes')
                ->toMediaCollection('previews')
                ->saveOrFail();

            return $next($video);
        });
    }
}
