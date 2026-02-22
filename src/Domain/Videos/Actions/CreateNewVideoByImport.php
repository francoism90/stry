<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\DataObjects\VideoFileData;
use Domain\Videos\Events\VideoHasBeenAddedEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateNewVideoByImport
{
    public function handle(User $user, VideoFileData $file): mixed
    {
        return DB::transaction(function () use ($user, $file) {
            // Get the file name without extension
            $fileName = File::name($file->path);

            // Create the video record
            $video = $user->videos()->create([
                'name' => Str::title($fileName),
            ]);

            // Attach the video clip
            $video
                ->addMediaFromDisk($file->path, $file->disk)
                ->toMediaCollection('clips');

            // Dispatch the added event
            VideoHasBeenAddedEvent::dispatch($video);
        });
    }
}
