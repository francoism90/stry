<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Domain\Transcodes\Models\Transcode;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use ProtoneMedia\LaravelFFMpeg\Support\StreamParser;

class GenerateMediaStoryboard
{
    /**
     * Upper bound on the number of sampled thumbnails, chosen so a single
     * tile grid (see below) always fits them without spilling into a second sprite image.
     */
    protected const MAX_THUMBNAILS = 100;

    protected const COLUMNS = 10;

    protected const ROWS = 10;

    protected const TILE_WIDTH = 160;

    protected const TILE_HEIGHT = 90;

    protected const MIN_INTERVAL = 5;

    /**
     * @return array{image: string, vtt: string}|null
     */
    public function handle(Media $media): ?array
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        $duration = (float) $ffmpeg->getFormat()->get('duration');

        if ($duration <= 0.0) {
            return null;
        }

        $interval = max(self::MIN_INTERVAL, (int) ceil($duration / self::MAX_THUMBNAILS));
        $thumbnails = min(self::COLUMNS * self::ROWS, (int) ceil($duration / $interval));

        $imagePath = "{$media->uuid}_storyboard.jpg";
        $vttPath = "{$media->uuid}_storyboard.vtt";

        // Select every Nth frame by index rather than by timestamp: a frame's PTS is quantized
        // by the frame rate and will almost never land on an exact multiple of $interval, so a
        // time-based "mod(t,N)" select would under-sample. Falls back to a fixed 1fps sampling
        // rate if the frame rate can't be determined.
        $videoStream = $ffmpeg->getVideoStream();
        $frameRate = (float) ($videoStream ? StreamParser::new($videoStream)->getFrameRate() ?? 1 : 1);
        $frameInterval = max(1, (int) round($frameRate * $interval));

        // Sample one frame every $interval seconds and tile them into a single sprite image.
        // "-frames:v 1" caps the output at one tile image, even if a trailing selected frame
        // would otherwise start a second (partial) one.
        $ffmpeg
            ->export()
            ->addFilter([
                '-vf',
                sprintf(
                    'select=not(mod(n\,%d)),scale=%d:%d,tile=%dx%d',
                    $frameInterval,
                    self::TILE_WIDTH,
                    self::TILE_HEIGHT,
                    self::COLUMNS,
                    self::ROWS,
                ),
                '-frames:v',
                '1',
            ])
            ->toDisk(Transcode::getDestinationDisk())
            ->save($imagePath);

        $ffmpeg->cleanupTemporaryFiles();

        Storage::disk(Transcode::getDestinationDisk())->put(
            $vttPath,
            $this->buildVtt($imagePath, $interval, $thumbnails),
        );

        return [
            'image' => $imagePath,
            'vtt' => $vttPath,
        ];
    }

    protected function buildVtt(string $imagePath, int $interval, int $thumbnails): string
    {
        $cues = Collection::times($thumbnails, function (int $thumb) use ($imagePath, $interval) {
            $index = $thumb - 1;
            $row = intdiv($index, self::COLUMNS);
            $column = $index % self::COLUMNS;

            $start = $this->formatTimestamp($index * $interval);
            $end = $this->formatTimestamp(($index + 1) * $interval);
            $x = $column * self::TILE_WIDTH;
            $y = $row * self::TILE_HEIGHT;

            return "{$start} --> {$end}\n{$imagePath}#xywh={$x},{$y},".self::TILE_WIDTH.','.self::TILE_HEIGHT;
        });

        return $cues->prepend('WEBVTT')->implode("\n\n");
    }

    protected function formatTimestamp(int $seconds): string
    {
        return sprintf('%02d:%02d:%02d.000', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }
}
