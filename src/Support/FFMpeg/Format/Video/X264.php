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
        return ['copy', 'aac', 'libvo_aacenc', 'libfaac', 'libmp3lame', 'libfdk_aac', 'libopus'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableVideoCodecs()
    {
        return ['copy', 'libx264', 'h264'];
    }
}
