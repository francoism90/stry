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

        // Get the streams from the media file
        $streams = FFMpeg::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->getStreams();

        // Parse the streams with only relevant keys
        $keys = $this->getStreamKeys();

        $items = collect($streams)
            ->map(fn (Stream $stream) => collect($stream->all())->only($keys)->toArray())
            ->filter();

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
