<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Actions\CleanupTemporaryCache;
use Domain\Media\Actions\CreateMediaSegments;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\X264;

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
            $paths = app(CreateMediaSegments::class)->handle($media);

            // Set the path for the sample video
            $path = "segments/{$media->uuid}/preview.mp4";

            // Create a sample video from the segments
            FFMpeg::fromDisk('cache')
                ->open($paths->toArray())
                ->export()
                ->inFormat((new X264)->setKiloBitrate(1500))
                ->concatWithTranscoding(hasAudio: false)
                ->toDisk('cache')
                ->save($path);

            // Add the sample video to the video model
            $video
                ->addMediaFromDisk($path, 'cache')
                ->preservingOriginal()
                ->toMediaCollection('previews')
                ->saveOrFail();

            // Clean up temporary files
            app(CleanupTemporaryCache::class)->handle($media, 'segments');

            return $next($video);
        });
    }
}
