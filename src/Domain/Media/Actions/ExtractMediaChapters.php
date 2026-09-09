<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Throwable;

class ExtractMediaChapters
{
    public function handle(Media $media): Collection
    {
        // Initialize FFMpeg
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open(
            $media->getPathRelativeToRoot(),
        );

        // Chapters aren't exposed by php-ffmpeg/laravel-ffmpeg, so we shell out
        // to ffprobe directly via the underlying driver
        try {
            $output = $ffmpeg->getFFProbe()->getFFProbeDriver()->command([
                $ffmpeg->getPathfile(),
                '-show_chapters',
                '-print_format', 'json',
                '-loglevel', 'error',
            ]);
        } catch (Throwable $e) {
            return Collection::make();
        }

        $chapters = json_decode($output, true);

        return Collection::make(data_get($chapters, 'chapters', []))
            ->map(fn (array $chapter): array => [
                'label' => data_get($chapter, 'tags.title'),
                'start_time' => (float) data_get($chapter, 'start_time', 0),
                'end_time' => (float) data_get($chapter, 'end_time', 0),
            ])
            ->filter(fn (array $chapter): bool => filled($chapter['label']) && $chapter['end_time'] > $chapter['start_time'])
            ->values();
    }
}
