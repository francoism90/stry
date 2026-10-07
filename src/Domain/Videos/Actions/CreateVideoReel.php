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
use Foxws\Media\Opener;
use Foxws\Media\Probe\VideoStream;
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

    /**
     * How bright the blurred background stays (0-1), so it doesn't compete with the picture.
     */
    public const float BACKGROUND_BRIGHTNESS = 0.5;

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
            ->addFilter($this->fit($opener), new Fps($this->settings->fps))
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
     * How the picture fits the reel's frame. Cropped, the zoom (0-100) decides how much of the frame a
     * wider picture fills: 100 fills all of it and cuts off the most of the sides, 0 shows the whole
     * picture, and in between it fills a band across the full width. The rest of the frame is a blurred,
     * darkened copy of the picture, made at a quarter of the size, which is much faster and looks the
     * same. Letterboxed, the whole picture gets black bars.
     */
    protected function fit(Opener $opener): Filter
    {
        $width = $this->settings->width;
        $height = $this->settings->height;

        if ($this->settings->fit === 'letterbox') {
            return Scale::fit($width, $height);
        }

        $band = $this->bandHeight($opener->probe()->videoStream());

        if ($band === null || $band >= $height) {
            return Scale::fill($width, $height);
        }

        return Custom::video(implode(';', [
            'split[background][foreground]',
            sprintf('[background]%s,boxblur=10:1,lutyuv=y=val*%s,scale=%d:%d[blurred]', Scale::fill(intdiv($width, 8) * 2, intdiv($height, 8) * 2), self::BACKGROUND_BRIGHTNESS, $width, $height),
            sprintf('[foreground]%s[fitted]', Scale::fill($width, $band)),
            '[blurred][fitted]overlay=(W-w)/2:(H-h)/2,setsar=1',
        ]));
    }

    /**
     * The height of the picture across the full width at the zoom, rounded to an even number, or null
     * when the size of the source isn't known.
     */
    protected function bandHeight(?VideoStream $stream): ?int
    {
        if (! $stream?->width || ! $stream->height) {
            return null;
        }

        $fitted = $this->settings->width * $stream->height / $stream->width;
        $band = $fitted + ($this->settings->height - $fitted) * $this->settings->zoom / 100;

        return (int) round($band / 2) * 2;
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
