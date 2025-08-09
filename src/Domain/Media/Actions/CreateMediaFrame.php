<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Illuminate\Support\Number;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CreateMediaFrame
{
    public function handle(Media $media, float $seconds = 10): string
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $seconds = Number::clamp($seconds, 0, $ffmpeg->getDurationInSeconds());

        $path = "{$media->uuid}_frame.jpg";

        $ffmpeg
            ->open($media->getPathRelativeToRoot())
            ->getFrameFromSeconds($seconds)
            ->export()
            ->toDisk('cache')
            ->save($path);

        return $path;
    }
}
