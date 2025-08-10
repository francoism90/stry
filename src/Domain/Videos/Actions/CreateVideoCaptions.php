<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Actions\CleanupTemporaryCache;
use Domain\Media\Actions\ExtractMediaCaptions;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class CreateVideoCaptions
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            if (! $video->hasCaptions() || ! $video->hasMedia('clips')) {
                return $next($video);
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Generate media captions
            $paths = app(ExtractMediaCaptions::class)->handle($media);

            // Add the sample video to the video model
            $paths->each(fn (string $path) => $video
                ->addMediaFromDisk($path, 'cache')
                ->toMediaCollection('captions')
            );

            $video->saveOrFail();

            // Clean up temporary files
            app(CleanupTemporaryCache::class)->handle($media, 'captions');

            return $next($video);
        });
    }
}
