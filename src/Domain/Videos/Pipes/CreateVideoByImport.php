<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Users\Models\User;
use Domain\Videos\DataObjects\VideoFile;
use Domain\Videos\Events\VideoHasBeenAddedEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateNewVideoByImport
{
    public function handle(User $user, VideoFile $file, Closure $next): mixed
    {
        return DB::transaction(function () use ($user, $file, $next) {
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

            return $next($video);
        });
    }
}
