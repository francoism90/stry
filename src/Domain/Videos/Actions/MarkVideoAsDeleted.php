<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class MarkVideoAsDeleted
{
    public function handle(Video $video): void
    {
        DB::transaction(function () use ($video) {
            $video->delete();

            return $video;
        });
    }
}
