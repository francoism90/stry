<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use FFMpeg\Format\Video\X264 as DefaultVideo;

class X265 extends DefaultVideo
{
    public function __construct($audioCodec = 'aac', $videoCodec = 'libx265')
    {
        $this
            ->setAudioCodec($audioCodec)
            ->setVideoCodec($videoCodec);
    }

    public function getAvailableAudioCodecs()
    {
        return ['copy', 'aac', 'libfdk_aac', 'libfaac', 'libmp3lame', 'libopus'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableVideoCodecs()
    {
        return ['copy', 'libx265', 'h265', 'hevc'];
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
            '-preset', 'medium',
            '-crf', '28',
            '-profile:v', 'main',
            '-level', '4.1',
            '-tag:v', 'hvc1',
            '-g', '240',
            '-keyint_min', '120',
            '-sc_threshold', '0',
            '-movflags', '+faststart',
            '-pix_fmt', 'yuv420p',
            '-x265-params', 'log-level=error',
        ];
    }
}
