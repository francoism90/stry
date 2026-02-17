<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class ImportVideoFile
{
    public function handle(Video $video, string $disk, string $path): mixed
    {
        return DB::transaction(function () use ($video, $disk, $path) {
            // Attach the video clip
            $video
                ->addMediaFromDisk($path, $disk)
                ->toMediaCollection('clips');

            // Dispatch an event to trigger any necessary processing
            VideoHasBeenUpdatedEvent::dispatch($video);
        });
    }
}
