<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class ExportVideoMedia
{
    /**
     * Copy every media file of the video to "<video>/<collection>/<file>" on the exports disk,
     * replacing a previous export of the same video.
     */
    public function handle(Video $video): void
    {
        $disk = static::getDisk();

        $directory = (string) $video->getRouteKey();

        $disk->deleteDirectory($directory);

        $video->getMedia('*')->each(function (Media $media) use ($disk, $directory): void {
            $path = $this->getExportPath($disk, $directory, $media);

            $disk->writeStream($path, $media->stream());
        });
    }

    public static function getDisk(): Filesystem
    {
        return Storage::disk('exports');
    }

    protected function getExportPath(Filesystem $disk, string $directory, Media $media): string
    {
        $path = "{$directory}/{$media->collection_name}/{$media->file_name}";

        if (! $disk->exists($path)) {
            return $path;
        }

        return "{$directory}/{$media->collection_name}/{$media->getKey()}-{$media->file_name}";
    }
}
