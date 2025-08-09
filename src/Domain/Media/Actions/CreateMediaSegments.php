<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\X264;

class CreateMediaSegments
{
    public function handle(Media $media): Collection
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $segments = collect($this->getSegments($ffmpeg->getDurationInSeconds()));

        return $segments->map(function (float $seconds, int $key) use ($ffmpeg, $media) {
            $path = "segments/{$media->uuid}/segment_{$key}.mp4";

            $ffmpeg
                ->export()
                ->inFormat((new X264)
                    ->setInitialParameters(['-ss', TimeCode::fromSeconds($seconds), '-t', TimeCode::fromSeconds(2)])
                    ->setKiloBitrate(1500)
                )
                ->addFilter(['-vf', 'scale=1280:720,setdar=dar=16/9'])
                ->toDisk('cache')
                ->save($path);

            return $path;
        });
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
