<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\ReelSettings;
use Foxws\Media\Encoding\Format;
use Foxws\Media\FFMpeg\Clip;
use Foxws\Media\Filters\Fps;
use Foxws\Media\Filters\Scale;
use Foxws\Media\MediaFactory;
use Illuminate\Support\Str;

/**
 * Joins the selected cuts of the best clip into an H.264 reel of the size and frame rate in the reel
 * settings (vertical 1080×1920 by default), cropped to fill the frame, and keeps it in the video's "reels" collection, replacing the previous reel.
 * The reel is encoded on the GPU in media.ladder.hardware, or on the CPU when that can't be opened.
 */
class CreateVideoReel
{
    /**
     * Seconds ffmpeg may run, below the job's timeout.
     */
    public const int TIMEOUT = 1500;

    public function __construct(
        protected readonly MediaFactory $media,
        protected readonly SelectReelClips $selectClips,
        protected readonly ReelSettings $settings,
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
            ->addFilter(Scale::fill($this->settings->width, $this->settings->height), new Fps($this->settings->fps))
            ->hardware()
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
