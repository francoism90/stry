<?php

declare(strict_types=1);

namespace Support\MediaLibrary\Conversions\ImageGenerators;

use Foxws\Media\MediaFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Conversions\ImageGenerators\Video as ImageGenerator;

class Video extends ImageGenerator
{
    public function convert(string $file, ?Conversion $conversion = null): ?string
    {
        $directory = pathinfo($file, PATHINFO_DIRNAME);

        $media = app(MediaFactory::class)
            ->fromDisk(Storage::build(['driver' => 'local', 'root' => $directory]))
            ->open(pathinfo($file, PATHINFO_BASENAME));

        $probe = $media->probe();

        if (! $probe->hasVideo()) {
            return null;
        }

        $duration = $probe->duration();

        // Determine at which second to extract the frame
        $seconds = $conversion ? $conversion->getExtractVideoFrameAtSecond() : 0;

        // If no specific second is set, default to the middle of the video
        $seconds = $seconds > 0 ? $seconds : round($duration / 2);

        $imageFile = pathinfo($file, PATHINFO_FILENAME).'.jpg';

        $media
            ->ffmpeg()
            ->frame((float) Number::clamp($seconds, 0, $duration))
            ->save($imageFile);

        return "{$directory}/{$imageFile}";
    }

    public function requirementsAreInstalled(): bool
    {
        return true;
    }

    /**
     * @return Collection<int, string>
     */
    public function supportedExtensions(): Collection
    {
        return Collection::make([
            'm4v',
            'mkv',
            'mov',
            'mp4',
            'webm',
        ]);
    }

    /**
     * @return Collection<int, string>
     */
    public function supportedMimeTypes(): Collection
    {
        return Collection::make([
            'video/av1',
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'video/webm',
            'video/x-m4v',
            'video/x-matroska',
        ]);
    }
}
