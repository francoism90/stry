<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\ReelSettings;
use Foxws\Media\Encoding\Format;
use Foxws\Media\FFMpeg\Clip;
use Foxws\Media\Filters\Custom;
use Foxws\Media\Filters\Filter;
use Foxws\Media\Filters\Fps;
use Foxws\Media\Filters\Scale;
use Foxws\Media\MediaFactory;
use Illuminate\Support\Str;

/**
 * Joins the selected cuts of the best clip into an H.264 (or HEVC or AV1) reel of the size and frame rate in the reel
 * settings (vertical 1080×1920 by default), fitted to the frame as the reel settings say, and keeps it in the video's "reels" collection, replacing the previous reel.
 * The reel is encoded on the CPU, or on the GPU in media.ladder.hardware when the reel settings turn it on
 * and it can be opened.
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
            ->addFilter($this->fit(), new Fps($this->settings->fps))
            ->when($this->settings->hardware, fn ($builder) => $builder->hardware())
            ->inFormat($this->format())
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

    /**
     * How the picture fits the reel's frame: whole and centred on a blurred, zoomed copy of itself, whole
     * with black bars, or cropped to fill the frame. The background is blurred at a quarter of the size,
     * which is much faster and looks the same.
     */
    protected function fit(): Filter
    {
        $width = $this->settings->width;
        $height = $this->settings->height;

        return match ($this->settings->fit) {
            'crop' => Scale::fill($width, $height),
            'letterbox' => Scale::fit($width, $height),
            default => Custom::video(implode(';', [
                'split[background][foreground]',
                sprintf('[background]%s,boxblur=10:1,scale=%d:%d[blurred]', Scale::fill(intdiv($width, 8) * 2, intdiv($height, 8) * 2), $width, $height),
                sprintf('[foreground]scale=%d:%d:force_original_aspect_ratio=decrease[fitted]', $width, $height),
                '[blurred][fitted]overlay=(W-w)/2:(H-h)/2,setsar=1',
            ])),
        };
    }

    /**
     * The codec and quality in the reel settings. HEVC is tagged hvc1 so Safari plays it.
     */
    protected function format(): Format
    {
        return match ($this->settings->codec) {
            'av1' => Format::av1(crf: $this->settings->quality),
            'hevc' => Format::hevc(crf: $this->settings->quality),
            default => Format::h264(crf: $this->settings->quality),
        };
    }
}
