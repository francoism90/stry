<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Chapters\Actions\CreateChapter;
use Domain\Media\Actions\ExtractMediaChapters;
use Domain\Videos\Models\Video;

class ExtractVideoChapters
{
    public function handle(Video $video, Closure $next): mixed
    {
        // If the video already has chapters or has no clips, skip processing
        if ($video->chapters->isNotEmpty() || ! $video->hasMedia('clips')) {
            return $next($video);
        }

        // Get the first media item from the video
        $media = $video->getClips()->first();

        // Extract chapters from the media and create them for the video
        app(ExtractMediaChapters::class)
            ->handle($media)
            ->each(fn (array $attributes) => app(CreateChapter::class)->handle($video, $attributes));

        return $next($video);
    }
}
