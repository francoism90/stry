<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\X264;

class CreateMediaSegments
{
    public function handle(Media $media): string
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open(
            $media->getPathRelativeToRoot()
        );

        $path = "{$media->uuid}_sample.mp4";

        // Export each segment to a temporary file
        $segments = $this->getSegments($ffmpeg->getDurationInSeconds())->map(function (int|float $seconds, int $key) use ($ffmpeg, $media) {
            // Define segment path
            $segment = "{$media->uuid}_segment_{$key}.mp4";

            // Export the segment
            $ffmpeg
                ->export()
                ->toDisk('transcodes')
                ->inFormat((new X264)
                    ->setInitialParameters(['-ss', TimeCode::fromSeconds($seconds), '-t', TimeCode::fromSeconds(2)])
                    ->setAdditionalParameters(['-reset_timestamps', '1', '-an'])
                    ->setKiloBitrate(1500)
                )
                ->addFilter(['-vf', 'scale=1280:720,setdar=dar=16/9'])
                ->save($segment);

            return $segment;
        });

        // Concatenate segments into a single video
        FFMpeg::fromDisk('transcodes')
            ->open($segments->toArray())
            ->export()
            ->inFormat((new X264)->setKiloBitrate(1500))
            ->concatWithTranscoding(hasAudio: false)
            ->toDisk('transcodes')
            ->save($path);

        // Clean up temporary segment files
        $segments->each(fn (string $segment) => Storage::disk('transcodes')->delete($segment));

        return $path;
    }

    protected function getSegments(int|float $duration, int $count = 8): Collection
    {
        $segments = range(0, $duration, $duration / $count);

        $items = Collection::make($segments)
            ->map(fn (float $segment) => ceil($segment))
            ->unique()
            ->take($count);

        $items->shift();
        $items->pop();

        return $items;
    }
}
