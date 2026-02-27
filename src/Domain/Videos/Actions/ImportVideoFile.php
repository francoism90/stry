<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\DataObjects\VideoFile;
use Domain\Videos\Events\VideoHasBeenUpdatedEvent;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class ImportVideoFile
{
    public function handle(Video $video, VideoFile $file): mixed
    {
        return DB::transaction(function () use ($video, $file) {
            // Attach the video clip
            $video
                ->addMediaFromDisk($file->path, $file->disk)
                ->toMediaCollection('clips');

            // Dispatch an event to trigger any necessary processing
            VideoHasBeenUpdatedEvent::dispatch($video);
        });
    }
}
