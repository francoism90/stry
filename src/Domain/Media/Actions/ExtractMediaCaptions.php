<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ExtractMediaCaptions
{
    public function handle(Media $media): array
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $captions = $ffmpeg->getStreams();

        dd($captions);

        return [];
    }
}
