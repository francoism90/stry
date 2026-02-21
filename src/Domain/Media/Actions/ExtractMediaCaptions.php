<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Domain\Transcodes\Models\Transcode;
use Exception;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Collection;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\WebVTT;

class ExtractMediaCaptions
{
    public function handle(Media $media): Collection
    {
        // Initialize FFMpeg
        $ffmpeg = FFMpeg::fromDisk($media->disk)->open(
            $media->getPathRelativeToRoot(),
        );

        $items = Collection::make($ffmpeg->getStreams())
            ->filter(fn (Stream $stream) => $stream->get('codec_type') === 'subtitle')
            ->map(function (Stream $stream) use ($ffmpeg, $media): ?string {
                // Generate a unique filename for the extracted caption stream
                $index = $stream->get('index', 0);
                $language = data_get($stream->get('tags', []), 'language', 'und');
                $path = "{$media->uuid}_{$index}_{$language}.vtt";

                // Export the caption stream to a WebVTT file
                // If the conversion fails (e.g., due to unsupported codec), return null for this stream
                try {
                    $ffmpeg
                        ->export()
                        ->toDisk(Transcode::getDestinationDisk())
                        ->inFormat(new WebVTT)
                        ->addFilter(['-map', "0:{$index}"])
                        ->save($path);
                } catch (Exception $e) {
                    return null;
                }

                return $path;
            })
            ->filter()
            ->values();

        // Cleanup temporary files used during extraction
        $ffmpeg->cleanupTemporaryFiles();

        return $items;
    }
}
