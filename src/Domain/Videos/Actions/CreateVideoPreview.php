<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Filters\Video\VideoFilters;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\CopyVideoFormat;
use ProtoneMedia\LaravelFFMpeg\MediaOpener;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\X264;

class CreateVideoPreview
{
    public function handle(Video $video): mixed
    {
        return DB::transaction(function () use ($video) {
            if ($video->hasMedia('preview') || ! $video->hasMedia('clips')) {
                return;
            }

            /** @var Media $media */
            $media = $video->getClipCollection()->first();

            $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

            $segments = $this->getSegments($ffmpeg->getDurationInSeconds());

            $items = [];

            $ffmpeg->each($segments, function (MediaOpener $ffmpeg, float $seconds, int $key) use ($media, &$items) {
                $items[] = $path = "{$media->uuid}/preview_{$key}.mp4";

                return $ffmpeg->addFilter(fn (VideoFilters $filters) => $filters
                    ->clip(TimeCode::fromSeconds($seconds), TimeCode::fromSeconds(2)))
                    ->export()
                    ->inFormat((new CopyVideoFormat)->setAdditionalParameters(['-an', '-reset_timestamps', '1']))
                    ->toDisk('cache')
                    ->save($path);
            });

            // Concatenate the segments into a single preview video
            FFMpeg::fromDisk('cache')
                ->open($items)
                ->export()
                ->inFormat(new X264)
                ->concatWithTranscoding(hasAudio: false)
                ->toDisk('cache')
                ->save("{$media->uuid}/preview.mp4");

            // Add the preview video to the media collection
            $video
                ->addMediaFromDisk("{$media->uuid}/preview.mp4", 'cache')
                ->toMediaCollection('previews')
                ->saveOrFail();

            // Delete the temporary preview directory
            Storage::disk('cache')->deleteDirectory($media->uuid);
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
