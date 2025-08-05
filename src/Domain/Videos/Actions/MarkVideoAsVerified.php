<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Models\Video;
use Domain\Videos\States\Verified;
use Illuminate\Support\Facades\DB;

class MarkVideoAsVerified
{
    public function handle(Video $video): void
    {
        DB::transaction(function () use ($video) {
            //
            $video->touch('published_at');

            // Set state to verified if it can transition
            if ($video->state->canTransitionTo(Verified::class)) {
                $video->state->transitionTo(Verified::class);
            }

            return $video;
        });
    }
}
