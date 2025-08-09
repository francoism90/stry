<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\WebVTT;

class ExtractMediaCaptions
{
    public function handle(Media $media): Collection
    {
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

        return collect($ffmpeg->getStreams())
            ->filter(fn (Stream $stream) => $stream->get('codec_type') === 'subtitle')
            ->map(function (Stream $stream) use ($ffmpeg, $media) {
                $index = $stream->get('index', 0);
                $language = data_get($stream->get('tags', []), 'language', 'und');
                $path = "captions/{$media->uuid}/{$index}-{$language}.vtt";

                $ffmpeg
                    ->export()
                    ->toDisk('cache')
                    ->inFormat(new WebVTT)
                    ->addFilter(['-map', "0:{$index}"])
                    ->save($path);

                return $path;
            });
    }
}
