<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use Foxws\Media\MediaFactory;
use Foxws\Media\Probe\Stream;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SetMediaStreams
{
    public function __construct(
        protected MediaFactory $media,
    ) {}

    public function handle(Media $media): void
    {
        if (! Str::startsWith($media->mime_type, ['audio/', 'video/'])) {
            return;
        }

        $probe = $this->media
            ->fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->probe();

        // Map the streams to only include relevant keys
        $keys = $this->getStreamKeys();

        // Collect the stream items
        $items = Collection::make($probe->streams)
            ->map(fn (Stream $stream) => collect($stream->toArray())->only($keys)->toArray())
            ->filter()
            ->values();

        // Fill missing key values in each stream from the format
        Collection::make($probe->format()->raw)
            ->only($keys)
            ->each(function ($value, $key) use ($items) {
                $items->transform(function ($item) use ($key, $value) {
                    if (blank($item[$key] ?? null)) {
                        $item[$key] = $value;
                    }

                    return $item;
                });
            });

        // Update the media item with the streams
        $media
            ->setCustomProperty('streams', $items->toArray())
            ->saveOrFail();
    }

    /**
     * @return list<string>
     */
    protected function getStreamKeys(): array
    {
        return [
            'index',
            'codec_name',
            'codec_type',
            'width',
            'height',
            'bit_rate',
            'sample_rate',
            'duration',
            'closed_captions',
            'channels',
            'channel_layout',
        ];
    }
}
