<?php

declare(strict_types=1);

namespace Domain\Media\Listeners;

use Domain\Media\Models\Media;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class SetMediaStreamData
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        if (! Str::startsWith($event->media->mime_type, ['audio/', 'video/'])) {
            return;
        }

        $keys = $this->getStreamKeys();

        $streams = $this->parseStreams($event->media, $keys);

        $event->media->setCustomProperty('streams', $streams->toArray());
        $event->media->saveOrFail();
    }

    protected function parseStreams(Media $media, array $keys): Collection
    {
        $streams = FFMpeg::fromDisk($media->disk)
            ->open($media->getPathRelativeToRoot())
            ->getStreams();

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
