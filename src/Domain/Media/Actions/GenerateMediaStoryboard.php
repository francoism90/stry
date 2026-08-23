<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Domain\Transcodes\Models\Transcode;
use ProtoneMedia\LaravelFFMpeg\Filters\TileFactory;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

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

        $imagePath = "{$media->uuid}_storyboard.jpg";
        $vttPath = "{$media->uuid}_storyboard.vtt";

        $ffmpeg
            ->exportTile(function (TileFactory $factory) use ($interval, $vttPath) {
                $factory
                    ->interval($interval)
                    ->scale(self::TILE_WIDTH, self::TILE_HEIGHT)
                    ->grid(self::COLUMNS, self::ROWS)
                    ->generateVTT($vttPath);
            })
            ->toDisk(Transcode::getDestinationDisk())
            ->save($imagePath);

        $ffmpeg->cleanupTemporaryFiles();

        return [
            'image' => $imagePath,
            'vtt' => $vttPath,
        ];
    }
}
