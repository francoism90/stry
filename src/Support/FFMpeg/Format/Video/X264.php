<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use FFMpeg\Format\Video\X264 as DefaultVideo;

class X264 extends DefaultVideo
{
    public function getAvailableAudioCodecs()
    {
        return ['copy', 'aac', 'libvo_aacenc', 'libfaac', 'libmp3lame', 'libfdk_aac', 'libopus'];
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
            '-preset', 'veryfast',
            '-crf', '23',
            '-profile:v', 'high',
            '-level', '4.1',
            '-g', '240',
            '-keyint_min', '120',
            '-sc_threshold', '0',
            '-movflags', '+faststart',
            '-pix_fmt', 'yuv420p',
        ];
    }
}
