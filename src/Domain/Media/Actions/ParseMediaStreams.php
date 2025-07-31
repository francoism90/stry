<?php

declare(strict_types=1);

namespace Domain\Media\Actions;

use Domain\Media\Models\Media;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ParseMediaStreams
{
    public function handle(Media $media): Collection
    {
        if (! Str::startsWith($media->mime_type, ['audio/', 'video/'])) {
            return collect();
        }

        $streams = FFMpeg::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->getStreams();

        $keys = $this->getStreamKeys();

        return collect($streams)
            ->map(fn (Stream $stream) => collect($stream->all())->only($keys)->toArray())
            ->filter();
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
