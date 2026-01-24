<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Closure;
use Domain\Media\Models\Media;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class SetMediaStreams
{
    public function handle(Media $media, Closure $next): mixed
    {
        if (! Str::startsWith($media->mime_type, ['audio/', 'video/'])) {
            return $next($media);
        }

        // Parse the media streams
        $streams = FFMpeg::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->getStreams();

        $format = FFMpeg::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->getFormat();

        // Map the streams to only include relevant keys
        $keys = $this->getStreamKeys();

        $items = collect($streams)
            ->map(fn (Stream $stream) => collect($stream->all())->only($keys)->toArray())
            ->filter()
            ->values();

        // Fill missing key values in each stream from the format
        collect($format->all())
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

        return $next($media);
    }

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
