<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\Models\Video;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FetchImportableVideos
{
    public function handle(string $disk = 'import'): Collection
    {
        // Retrieve the collection of video files from the specified disk
        $fileSystem = Storage::disk($disk ?? Video::getImportDisk());

        return Collection::make($fileSystem->allFiles())
            ->filter(fn (string $path) => rescue(fn () => str_starts_with($fileSystem->mimeType($path), 'video/'), report: false))
            ->sort()
            ->take(Video::getImportBatchSize());
    }
}
