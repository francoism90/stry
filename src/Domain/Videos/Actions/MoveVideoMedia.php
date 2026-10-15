<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MoveVideoMedia
{
    /**
     * The collections whose original files are moved. Conversions, thumbnails and reels stay on
     * their own disks.
     *
     * @var array<int, string>
     */
    public const array COLLECTIONS = ['clips', 'captions'];

    /**
     * Move the clips and captions of the video to another disk. The media records are kept and
     * only their disk changes, so nothing is processed again.
     *
     * @throws RuntimeException when a moved file does not match its source
     */
    public function handle(Video $video, string $disk): void
    {
        $video->getMedia('*')
            ->filter(fn (Media $media): bool => in_array($media->collection_name, self::COLLECTIONS, true))
            ->reject(fn (Media $media): bool => $media->disk === $disk)
            ->each(fn (Media $media) => $this->move($media, $disk));
    }

    protected function move(Media $media, string $disk): void
    {
        $path = $media->getPathRelativeToRoot();

        $source = Storage::disk($media->disk);
        $target = Storage::disk($disk);

        $stream = $media->stream()
            ?? throw new RuntimeException("Media [{$media->getKey()}] could not be read from [{$media->disk}].");

        $target->writeStream($path, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        throw_unless(
            $target->exists($path) && $target->size($path) === $source->size($path),
            RuntimeException::class,
            "Moved file [{$path}] does not match media [{$media->getKey()}].",
        );

        $media->disk = $disk;
        $media->saveQuietly();

        $source->delete($path);
    }
}
