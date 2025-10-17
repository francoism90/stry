<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Events\VideoHasBeenAddedEvent;
use Illuminate\Support\Facades\DB;
use SplFileInfo;

class CreateNewVideoByImport
{
    public function handle(User $user, string $disk, string $path): mixed
    {
        return DB::transaction(function () use ($user, $disk, $path) {
            // Parse the file info
            $file = new SplFileInfo($path);
            $name = $file->getBasename(".{$file->getExtension()}");

            // Create the video record
            $video = $user->videos()->create([
                'name' => $name,
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
