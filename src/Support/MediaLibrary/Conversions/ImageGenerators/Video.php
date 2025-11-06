<?php

declare(strict_types=1);

namespace Support\MediaLibrary\Conversions\ImageGenerators;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video as FFMpegVideo;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
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

        // Get the duration of the video in seconds
        $duration = $ffmpeg->getFFProbe()->format($file)->get('duration');

        // Determine at which second to extract the frame
        $seconds = $conversion ? $conversion->getExtractVideoFrameAtSecond() : 0;

        // If no specific second is set, default to the middle of the video
        $seconds = $seconds > 0 ? $seconds : round($duration / 2);

        // Clamp the seconds to be within the video duration
        $quantity = Number::clamp($seconds, 0, $duration);

        $imageFile = pathinfo($file, PATHINFO_DIRNAME).'/'.pathinfo($file, PATHINFO_FILENAME).'.jpg';

        $frame = $video->frame(TimeCode::fromSeconds($quantity));
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
            'm4v',
            'mka',
            'mkv',
            'mov',
            'mp4',
            'mp4v-es',
            'webm',
        ]);
    }

    public function supportedMimeTypes(): Collection
    {
        return Collection::make([
            'video/av1',
            'video/mkv',
            'video/mp4',
            'video/mp4v-es',
            'video/quicktime',
            'video/webm',
            'video/x-m4v',
            'video/x-matroska',
            'video/x-msvideo',
        ]);
    }
}
