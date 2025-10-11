<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Illuminate\Support\Number;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class CreateMediaFrame
{
    public function handle(Media $media, float $seconds = 0): TemporaryDirectory
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $temporaryDirectory = TemporaryDirectory::make('transcodes');

        $duration = $ffmpeg->getDurationInSeconds();

        $seconds = $seconds > 0 ? $seconds : $duration / 2;

        $frame = Number::clamp($seconds, 0, $duration);

        $ffmpeg
            ->getFrameFromSeconds(round($frame, 2))
            ->export()
            ->toDisk('transcodes')
            ->save($temporaryDirectory->path('frame.webp'));

        return $temporaryDirectory;
    }
}
