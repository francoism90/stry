<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Users\Models\User;
use Domain\Videos\Jobs\ImportVideo;
use Domain\Videos\Models\Video;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class CreateVideosByImport
{
    public function handle(User $user, string $disk = 'import'): array
    {
        $files = $this->getCollection($disk);

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
            ->map(fn (string $path) => new ImportVideo($user, $disk, $path))
            ->all()
        )->dispatch();

        return [
            'success' => true,
            'message' => "Importing {$files->count()} video(s).",
            'count' => $files->count(),
            'batch_id' => $batch->id,
        ];
    }

    public function getCollection(string $disk): Collection
    {
        return Collection::make($this->getFileSystem($disk)->allFiles())
            ->filter(fn (string $path) => rescue(fn () => str_starts_with($this->getFileSystem($disk)->mimeType($path), 'video/'), report: false))
            ->sort()
            ->take(Video::getImportBatchSize());
    }

    protected function getFileSystem(string $disk): FilesystemAdapter
    {
        return Storage::disk($disk);
    }
}
