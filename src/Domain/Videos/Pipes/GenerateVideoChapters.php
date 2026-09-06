<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Chapters\Actions\GenerateChapterVtt;
use Domain\Videos\Models\Video;

class GenerateVideoChapters
{
    public function handle(Video $video, Closure $next): mixed
    {
        if ($video->chapters->isEmpty()) {
            $video->clearMediaCollection('chapters');

            return $next($video);
        }

        $video
            ->addMediaFromString(app(GenerateChapterVtt::class)->handle($video))
            ->usingFileName('chapters.vtt')
            ->toMediaCollection('chapters');

        return $next($video);
    }
}
