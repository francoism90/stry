<?php

declare(strict_types=1);

namespace Support\MediaLibrary;

use DateTimeInterface;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class MediaTemporaryUrl
{
    public function __construct(
        protected readonly Media $media,
        protected readonly DateTimeInterface $expiration,
    ) {}

    public static function make(Media $media, ?DateTimeInterface $expiration = null): static
    {
        return new static($media, $expiration ?? now()->addWeek());
    }

    public function getUrl(string $conversion = ''): string
    {
        return $this->media->getTemporaryUrl($this->expiration, $conversion);
    }

    public function getSrcset(string $conversion = ''): ?string
    {
        $responsiveImages = $this->media->responsiveImages($conversion);

        if ($responsiveImages->files->isEmpty()) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->media->conversions_disk);
        $basePath = PathGeneratorFactory::create($this->media)->getPathForResponsiveImages($this->media);

        return $responsiveImages->files
            ->map(fn (ResponsiveImage $image) => sprintf('%s %dw', $disk->temporaryUrl($basePath.$image->fileName, $this->expiration), $image->width()))
            ->implode(', ');
    }
}
