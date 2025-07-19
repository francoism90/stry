<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Domain\Media\Models\Media;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

trait InteractsWithVod
{
    public function getClipCollection(): MediaCollection
    {
        return $this->getMedia('clips')->sortBy([
            ['custom_properties->streams->bit_rate', 'desc'],
            ['custom_properties->streams->width', 'desc'],
            ['custom_properties->streams->height', 'desc'],
        ]);
    }

    public function getCaptionCollection(): MediaCollection
    {
        return $this->getMedia('captions');
    }

    public function getVideoStreams(): Collection
    {
        return $this
            ->getClipCollection()
            ->flatMap(fn (Media $media) => $media->getCustomProperty('streams', []))
            ->filter(fn (array $stream) => data_get($stream, 'codec_type') === 'video');
    }

    public function hasCaptions(): bool
    {
        if ($this->getCaptionCollection()->isNotEmpty()) {
            return true;
        }

        return (bool) data_get($this->getVideoStreams()->first(), 'closed_captions', false);
    }

    public function durationInSeconds(): float
    {
        return (float) data_get($this->getVideoStreams()->first(), 'duration', 0);
    }

    protected function captions(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->hasCaptions()
        )->shouldCache();
    }

    protected function duration(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->durationInSeconds()
        )->shouldCache();
    }

    protected function timestamp(): Attribute
    {
        return Attribute::make(
            get: fn () => duration($this->duration)
        )->shouldCache();
    }

    public function fileSize(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getClipCollection()->totalSizeInBytes(),
        )->shouldCache();
    }
}
