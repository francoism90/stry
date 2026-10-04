<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Foxws\Media\FFMpeg\ThumbnailsResult;
use Illuminate\Support\Facades\Config;

/**
 * Thumbnail sprite sheets sampled from the video, kept as a ThumbnailsResult in the thumbnails
 * column. Direct play offers them as image tracks for seek previews.
 */
trait InteractsWithThumbnails
{
    public static function bootInteractsWithThumbnails(): void
    {
        static::forceDeleted(fn (self $model) => $model->deleteThumbnails());
    }

    public static function getThumbnailsDisk(): string
    {
        return Config::string('videos.thumbnails_disk', 'conversions');
    }

    public function hasThumbnails(): bool
    {
        return $this->getThumbnails() !== null;
    }

    public function getThumbnails(): ?ThumbnailsResult
    {
        return is_array($this->thumbnails) && $this->thumbnails !== []
            ? ThumbnailsResult::fromArray($this->thumbnails)
            : null;
    }

    public function saveThumbnails(ThumbnailsResult $thumbnails): void
    {
        $this->forceFill(['thumbnails' => $thumbnails->toArray()])->saveQuietly();
    }

    public function deleteThumbnails(): void
    {
        $thumbnails = $this->getThumbnails();

        if ($thumbnails !== null) {
            $thumbnails->disk->filesystem()->delete($thumbnails->paths());
        }
    }
}
