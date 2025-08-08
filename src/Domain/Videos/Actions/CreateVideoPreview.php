<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Actions\CreateMediaSegments;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\X264;

class CreateVideoPreview
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            if ($video->hasPlaylists('preview') || ! $video->hasMedia('clips')) {
                return $next($video);
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Generate media segments
            $paths = app(CreateMediaSegments::class)->handle($media);

            // Create a sample video from the segments
            FFMpeg::fromDisk('cache')
                ->open($paths)
                ->export()
                ->inFormat((new X264)->setKiloBitrate(1500))
                ->concatWithTranscoding(hasAudio: false)
                ->toDisk('cache')
                ->save("{$media->uuid}_sample.mp4");

            // Add the sample video to the video model
            $video
                ->addMediaFromDisk("{$media->uuid}_sample.mp4", 'cache')
                ->usingFileName('preview.mp4')
                ->toMediaCollection('previews');

            $video->saveOrFail();

            // Clean up temporary files
            collect($paths)->each(fn (string $path) => Storage::disk('cache')->delete($path));

            return $next($video);
        });
    }
}
