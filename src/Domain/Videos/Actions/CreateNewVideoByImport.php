<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Events\VideoHasBeenAddedEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateNewVideoByImport
{
    public function handle(User $user, string $disk, string $path): mixed
    {
        return DB::transaction(function () use ($user, $disk, $path) {
            // Get the file name without extension
            $fileName = File::name($path);

            // Create the video record
            $video = $user->videos()->create([
                'name' => Str::title($fileName),
            ]);

            // Attach the video clip
            $video
                ->addMediaFromDisk($path, $disk)
                ->toMediaCollection('clips');

            // Dispatch the added event
            VideoHasBeenAddedEvent::dispatch($video);
        });
    }
}
