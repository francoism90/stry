<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use FFMpeg\Format\Video\X264 as DefaultVideo;

class X264 extends DefaultVideo
{
    /** @var int */
    private $passes = 1;

    public function getAvailableAudioCodecs()
    {
        return ['copy', 'aac', 'libfdk_aac', 'libfaac', 'libmp3lame', 'libopus'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableVideoCodecs()
    {
        return ['copy', 'libx264', 'h264'];
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
    public function getModulus()
    {
        return 2;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtraParams()
    {
        return [
            '-preset',
            'veryfast',
            '-sc_threshold',
            '0',
            '-pix_fmt',
            'yuv420p',
        ];
    }
}
