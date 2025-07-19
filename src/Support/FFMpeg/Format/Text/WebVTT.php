<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Text;

use FFMpeg\Format\Video\DefaultVideo;

class WebVTT extends DefaultVideo
{
    public function __construct($audioCodec = 'copy', $videoCodec = 'copy')
    {
        $this
            ->setAudioCodec($audioCodec)
            ->setVideoCodec($videoCodec);
    }

    /**
     * {@inheritDoc}
     */
    public function supportBFrames()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtraParams()
    {
        return ['-f', 'webvtt'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableAudioCodecs()
    {
        return ['copy'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableVideoCodecs()
    {
        return ['copy'];
    }
}
