<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Support\FFMpeg\Format\Video\X264;

class CreateMediaSegments
{
    public function handle(Media $media): TemporaryDirectory
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $temporaryDirectory = TemporaryDirectory::make();

        $segments = $this->getSegments($ffmpeg->getDurationInSeconds());

        // Export each segment to a temporary file
        $segments->map(function (float $seconds, int $key) use ($temporaryDirectory, $ffmpeg) {
            $path = $temporaryDirectory->path("segment_{$key}.mp4");

            $ffmpeg
                ->export()
                ->toDisk('transcodes')
                ->inFormat((new X264)
                    ->setInitialParameters(['-ss', TimeCode::fromSeconds($seconds), '-t', TimeCode::fromSeconds(2)])
                    ->setAdditionalParameters(['-reset_timestamps', '1', '-an'])
                    ->setKiloBitrate(1500)
                )
                ->addFilter(['-vf', 'scale=1280:720,setdar=dar=16/9'])
                ->save($path);

            return $path;
        });

        // Create a sample video from the segments
        FFMpeg::open($segments->toArray())
            ->export()
            ->toDisk('transcodes')
            ->inFormat((new X264)->setKiloBitrate(1500))
            ->concatWithTranscoding(hasAudio: false)
            ->save($temporaryDirectory->path('sample.mp4'));

        return $temporaryDirectory;
    }

    protected function getSegments(int|float $duration, int $count = 8): Collection
    {
        $segments = range(0, $duration, $duration / $count);

        $items = collect($segments)
            ->map(fn (float $segment) => ceil($segment))
            ->unique()
            ->take($count);

        $items->shift();
        $items->pop();

        return $items;
    }
}
