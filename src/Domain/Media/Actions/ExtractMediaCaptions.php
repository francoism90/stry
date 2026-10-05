<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Domain\Transcodes\Models\Transcode;
use Foxws\Media\Encoding\Format;
use Foxws\Media\MediaFactory;
use Foxws\Media\Probe\SubtitleStream;
use Illuminate\Support\Collection;
use Throwable;

class ExtractMediaCaptions
{
    public function __construct(
        protected MediaFactory $media,
    ) {}

    /**
     * @return Collection<int, non-falsy-string>
     */
    public function handle(Media $media): Collection
    {
        $opener = $this->media
            ->fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot());

        try {
            return Collection::make($opener->probe()->subtitleStreams())
                ->map(function (SubtitleStream $stream) use ($opener, $media): ?string {
                    $language = $stream->language ?? 'und';
                    $path = "{$media->uuid}_{$stream->index}_{$language}.vtt";

                    // Streams that can't be converted, such as bitmap subtitles, are skipped
                    try {
                        $opener
                            ->ffmpeg()
                            ->map("0:{$stream->index}")
                            ->inFormat(Format::webVtt())
                            ->toDisk(Transcode::getDestinationDisk())
                            ->save($path);
                    } catch (Throwable) {
                        return null;
                    }

                    return $path;
                })
                ->filter()
                ->values();
        } finally {
            $opener->cleanupTemporaryFiles();
        }
    }
}
