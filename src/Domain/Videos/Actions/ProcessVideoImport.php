<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Jobs\CreateVideo;
use Illuminate\Support\Facades\Bus;

class ProcessVideoImport
{
    public function handle(User $user, string $disk = 'import'): array
    {
        // Fetch the collection of video files from the specified disk
        $files = app(FetchImportableVideos::class)->handle($disk);

        if ($files->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No video files found in the import directory.',
                'count' => 0,
                'batch_id' => null,
            ];
        }

        // Dispatch batch of import jobs
        $batch = Bus::batch($files
            ->map(fn (string $path) => CreateVideo::dispatch($user, $disk, $path))
            ->all()
        )->dispatch();

        return [
            'success' => true,
            'message' => "Importing {$files->count()} video(s).",
            'count' => $files->count(),
            'batch_id' => $batch->id,
        ];
    }
}
