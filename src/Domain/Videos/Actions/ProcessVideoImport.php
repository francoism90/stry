<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\DataObjects\VideoFile;
use Domain\Videos\Jobs\CreateVideo;
use Illuminate\Support\Facades\Bus;

class ProcessVideoImport
{
    public function handle(User $user, string $disk): void
    {
        // Fetch the collection of video files from the specified disk
        $files = app(FetchImportableVideos::class)->handle($disk);

        if ($files->isEmpty()) {
            return;
        }

        // Create a batch of jobs to process each video file
        Bus::batch($files
            ->map(fn (VideoFile $file) => CreateVideo::dispatch($user, $file))
            ->all()
        )->dispatch();
    }
}
