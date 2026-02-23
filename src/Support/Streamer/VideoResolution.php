<?php

declare(strict_types=1);

namespace Support\Streamer;

use FFMpeg\Coordinate\Dimension;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoResolution
{
    /** @var array<int, string> */
    private const RESOLUTIONS = [
        144  => '144p',
        240  => '240p',
        360  => '360p',
        480  => '480p',
        576  => '576p',
        720  => '720p',
        1080 => '1080p',
        1440 => '1440p',
        2160 => '4k',
        4320 => '8k',
    ];

    public function __construct(
        protected ?string $disk = null,
        protected ?string $path = null,
    ) {}

    public static function make(?string $disk = null, ?string $path = null): static
    {
        return new static($disk, $path);
    }

    public function first(string $disk, string $path): ?string
    {
        return $this->all($disk, $path)->first();
    }

    /** @return Collection<int, string> */
    public function all(string $disk, string $path): Collection
    {
        $stream = FFMpeg::fromDisk($disk)->open($path)->getVideoStream();

        if (! $stream) {
            return Collection::make();
        }

        /** @var Dimension $dimensions */
        $dimensions = $stream->getDimensions();

        return $this->allFromHeight($dimensions->getHeight());
    }

    /** @return Collection<int, string> */
    public function allFromHeight(int $height): Collection
    {
        return Collection::make(self::RESOLUTIONS)
            ->filter(fn (string $name, int $maxHeight) => $height >= $maxHeight)
            ->values();
    }

    public function firstFromHeight(int $height): ?string
    {
        return $this->allFromHeight($height)->first();
    }

    /** @return array<int, string> */
    public function toArray(): array
    {
        if (! $this->disk || ! $this->path) {
            return [];
        }

        return $this->all($this->disk, $this->path)->all();
    }
}
