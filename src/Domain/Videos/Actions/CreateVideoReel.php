<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Foxws\Media\Encoding\Format;
use Foxws\Media\FFMpeg\Clip;
use Foxws\Media\Filters\Fps;
use Foxws\Media\Filters\Scale;
use Foxws\Media\MediaFactory;
use Illuminate\Support\Str;

/**
 * Joins the selected cuts of the best clip into a vertical 1080×1920 H.264 reel, cropped to fill the
 * frame, and keeps it in the video's "reels" collection, replacing the previous reel.
 */
class CreateVideoReel
{
    public const int WIDTH = 1080;

    public const int HEIGHT = 1920;

    public const float FPS = 30.0;

    /**
     * Seconds ffmpeg may run, below the job's timeout.
     */
    public const int TIMEOUT = 1500;

    public function __construct(
        protected readonly MediaFactory $media,
        protected readonly SelectReelClips $selectClips,
    ) {}

    public function handle(Video $video): ?Media
    {
        $clip = $video->getClips()->first();

        if ($clip === null) {
            return null;
        }

        $opener = $this->media->fromDisk($clip->disk)->open($clip->getPathRelativeToRoot());

        $cuts = $this->selectClips->handle($video, $opener);

        if ($cuts === []) {
            return null;
        }

        $path = 'reels/'.Str::ulid().'.mp4';

        $opener
            ->ffmpeg()
            ->clips($cuts)
            ->toneMap()
            ->addFilter(Scale::fill(self::WIDTH, self::HEIGHT), new Fps(self::FPS))
            ->inFormat(Format::h264())
            ->withContext(['video_id' => $video->getKey()])
            ->timeout(self::TIMEOUT)
            ->save($path);

        /** @var Media */
        return $video
            ->addMediaFromDisk($path, $clip->disk)
            ->usingName((string) $video->title)
            ->usingFileName("{$video->getRouteKey()}.mp4")
            ->withCustomProperties([
                'clips' => array_map(fn (Clip $cut): array => [$cut->from, $cut->to], $cuts),
            ])
            ->toMediaCollection('reels');
    }
}
