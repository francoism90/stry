<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\DataObjects\VideoFile;
use Domain\Videos\Models\Video;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FetchImportableVideos
{
    public function handle(string $disk): Collection
    {
        // Get a filesystem instance for the specified disk
        $filesystem = Storage::disk($disk);

        return Collection::make($filesystem->allFiles())
            ->take(Video::getImportBatchSize())
            ->filter(fn (string $path) => rescue(fn () => str_starts_with($filesystem->mimeType($path), 'video/'), report: false))
            ->map(fn (string $path) => VideoFile::from([
                'disk' => $disk,
                'path' => $path,
                'name' => File::name($path),
                'size' => $filesystem->size($path),
            ]));
    }
}
