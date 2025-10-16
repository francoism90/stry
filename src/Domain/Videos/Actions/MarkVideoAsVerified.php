<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;
use Illuminate\Support\Facades\DB;

class MarkVideoAsVerified
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            // Transition the video state to verified if possible
            if ($video->state->canTransitionTo(Verified::class)) {
                $video->state->transitionTo(Verified::class);
            }

            // Update the published_at timestamp to the current time
            $video->touch('published_at');

            return $next($video);
        });
    }
}
