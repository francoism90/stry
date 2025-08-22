<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Illuminate\Support\Number;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CreateMediaFrame
{
    public function handle(Media $media, ?float $seconds = 0): string
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $duration = $ffmpeg->getDurationInSeconds();

        $seconds = $seconds > 1 ? $seconds : $duration / 2;

        $frame = Number::clamp($seconds, 0, $duration);

        $path = "frames/{$media->uuid}/thumb.jpg";

        $ffmpeg
            ->open($media->getPathRelativeToRoot())
            ->getFrameFromSeconds(round($frame, 2))
            ->export()
            ->toDisk('transcodes')
            ->save($path);

        return $path;
    }
}
