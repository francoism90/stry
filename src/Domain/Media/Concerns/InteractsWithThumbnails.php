<?php

declare(strict_types=1);

namespace Domain\Media\Concerns;

use Foxws\Media\FFMpeg\ThumbnailsResult;
use Illuminate\Support\Facades\Config;

/**
 * Thumbnail sprite sheets sampled from this clip, kept in its "thumbnails" custom property.
 * They follow the clip's own timeline, so every clip has its own, and direct play offers
 * them as image tracks for seek previews.
 */
trait InteractsWithThumbnails
{
    public static function bootInteractsWithThumbnails(): void
    {
        static::deleted(fn (self $model) => $model->deleteThumbnails());
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
        $thumbnails = $this->getCustomProperty('thumbnails');

        /** @var array<string, mixed>|null $thumbnails */
        return is_array($thumbnails) && $thumbnails !== [] ? ThumbnailsResult::fromArray($thumbnails) : null;
    }

    public function saveThumbnails(ThumbnailsResult $thumbnails): void
    {
        $this->setCustomProperty('thumbnails', $thumbnails->toArray())->saveQuietly();
    }

    public function deleteThumbnails(): void
    {
        $thumbnails = $this->getThumbnails();

        if ($thumbnails !== null) {
            $thumbnails->disk->filesystem()->delete($thumbnails->paths());
        }
    }
}
