<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Illuminate\Support\Facades\Storage;

class CleanupTemporaryCache
{
    public function handle(Media $media, string $path): void
    {
        $absolutePath = "{$path}/{$media->uuid}";

        if (Storage::disk('transcodes')->directoryExists($absolutePath)) {
            Storage::disk('transcodes')->deleteDirectory($absolutePath);
        }
    }
}
