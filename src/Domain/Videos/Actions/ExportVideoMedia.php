<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Shared\Enums\DiskContent;
use Domain\Videos\Models\Video;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ExportVideoMedia
{
    /**
     * Copy every media file of the video to "<video>/<collection>/<file>" on the exports disk,
     * replacing a previous export of the same video.
     *
     * @throws RuntimeException when an exported file does not match its source
     */
    public function handle(Video $video): void
    {
        $disk = static::getDisk();

        $directory = (string) $video->getRouteKey();

        $disk->deleteDirectory($directory);

        $video->getMedia('*')->each(function (Media $media) use ($disk, $directory): void {
            $path = $this->getExportPath($disk, $directory, $media);

            $disk->writeStream($path, $media->stream());

            $this->ensureExported($disk, $path, $media);
        });
    }

    public static function getDisk(): Filesystem
    {
        return Storage::disk(DiskContent::Exports->disk());
    }

    protected function ensureExported(Filesystem $disk, string $path, Media $media): void
    {
        $expectedSize = Storage::disk($media->disk)->size($media->getPathRelativeToRoot());

        throw_unless(
            $disk->exists($path) && $disk->size($path) === $expectedSize,
            RuntimeException::class,
            "Exported file [{$path}] does not match media [{$media->getKey()}].",
        );
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
