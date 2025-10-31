<?php

declare(strict_types=1);

namespace Support\MediaLibrary\Conversions\ImageGenerators;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video as FFMpegVideo;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Video as ImageGenerator;

class Video extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): ?string
    {
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => config('media-library.ffmpeg_path'),
            'ffprobe.binaries' => config('media-library.ffprobe_path'),
            'timeout' => config('media-library.ffmpeg_timeout', 900),
            'ffmpeg.threads' => config('media-library.ffmpeg_threads', 0),
        ]);

        $video = $ffmpeg->open($file);

        if (! ($video instanceof FFMpegVideo)) {
            return null;
        }

        $duration = $ffmpeg->getFFProbe()->format($file)->get('duration');

        $seconds = $conversion ? $conversion->getExtractVideoFrameAtSecond() : 0;
        $seconds = $duration <= $seconds ? 0 : $seconds;

        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        $frame = $video->frame(TimeCode::fromSeconds($seconds));
        $frame->save($imageFile);

        return $imageFile;
    }

    public function requirementsAreInstalled(): bool
    {
        return class_exists('\\FFMpeg\\FFMpeg');
    }

    public function supportedExtensions(): Collection
    {
        return Collection::make([
            'av1',
            'avi',
            'mkv',
            'mp4',
            'mp4v-es',
            'mpeg',
            'ogg',
            'mov',
            'webm',
            'flv',
            'm4v',
            'mka',
            'mpg',
            'asf',
            'wmv',
        ]);
    }

    public function supportedMimeTypes(): Collection
    {
        return Collection::make([
            'video/av1',
            'video/avi',
            'video/mkv',
            'video/mp4',
            'video/mp4v-es',
            'video/mpeg',
            'video/ogg',
            'video/quicktime',
            'video/webm',
            'video/x-flv',
            'video/x-m4v',
            'video/x-matroska',
            'video/x-mpeg',
            'video/x-ms-asf',
            'video/x-ms-wmv',
            'video/x-msvideo',
        ]);
    }
}
