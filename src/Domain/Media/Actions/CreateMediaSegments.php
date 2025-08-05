<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Video\VideoFilters;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\CopyVideoFormat;
use ProtoneMedia\LaravelFFMpeg\MediaOpener;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CreateMediaSegments
{
    public function handle(Media $media): array
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        $segments = $this->getSegments($ffmpeg->getDurationInSeconds());

        $items = [];

        $ffmpeg->each($segments, function (MediaOpener $ffmpeg, float $seconds, int $key) use ($media, $extension, &$items) {
            $items[] = $path = "media_{$media->uuid}_{$key}.{$extension}";

            return $ffmpeg->addFilter(fn (VideoFilters $filters) => $filters
                ->clip(TimeCode::fromSeconds($seconds), TimeCode::fromSeconds(2)))
                ->export()
                ->inFormat((new CopyVideoFormat)->setAdditionalParameters(['-an', '-reset_timestamps', '1']))
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
