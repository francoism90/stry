<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use FFMpeg\Coordinate\TimeCode;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\CopyVideoFormat;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CreateMediaSegments
{
    public function handle(Media $media): array
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $items = [];

        $segments = collect($this->getSegments($ffmpeg->getDurationInSeconds()));

        $segments->each(function (float $seconds, int $key) use ($ffmpeg, $media, &$items) {
            $items[] = $path = "{$media->uuid}_segment_{$key}.mp4";

            $ffmpeg
                ->export()
                ->inFormat((new CopyVideoFormat)
                    ->setInitialParameters(['-ss', TimeCode::fromSeconds($seconds), '-t', TimeCode::fromSeconds(2)])
                    ->setKiloBitrate(1500)
                )
                ->toDisk('cache')
                ->save($path);
        });

        return $items;
    }

    protected function getSegments(int $duration, int $count = 10): array
    {
        $segments = range(0, $duration, $duration / $count);

        $items = collect($segments)
            ->map(fn (float $segment) => ceil($segment))
            ->unique()
            ->take($count);

        $items->shift();
        $items->pop();

        return $items->toArray();
    }
}
