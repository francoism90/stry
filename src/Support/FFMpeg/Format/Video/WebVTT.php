<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use FFMpeg\Format\Video\DefaultVideo;

class WebVTT extends DefaultVideo
{
    public function __construct(string $audioCodec = 'copy', string $videoCodec = 'copy')
    {
        $this
            ->setAudioCodec($audioCodec)
            ->setVideoCodec($videoCodec);
    }

    /**
     * @return list<string>
     */
    public function getAvailableAudioCodecs(): array
    {
        return ['copy'];
    }

    /**
     * @return list<string>
     */
    public function getAvailableVideoCodecs(): array
    {
        return ['copy'];
    }

    /**
     * {@inheritDoc}
     */
    public function supportBFrames()
    {
        return false;
    }

    /**
     * @return list<string>
     */
    public function getExtraParams(): array
    {
        return ['-f', 'webvtt'];
    }
}
