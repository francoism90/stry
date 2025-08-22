<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;
use Illuminate\Support\Facades\DB;

class MarkVideoAsVerified
{
    public function handle(Video $video, Closure $next): void
    {
        DB::transaction(function () use ($video, $next) {
            if ($video->state->canTransitionTo(Verified::class)) {
                $video->state->transitionTo(Verified::class);
            }

            return $next($video);
        });
    }
}
