<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;

class MarkVideoAsVerified
{
    public function handle(Video $video, Closure $next): mixed
    {
        // Transition the video state if possible
        if ($video->state->canTransitionTo(Verified::class)) {
            $video->state->transitionTo(Verified::class);
        }

        // Set the published_at timestamp if not already set
        if (blank($video->published_at)) {
            $video->touch('published_at');
        }

        return $next($video);
    }
}
