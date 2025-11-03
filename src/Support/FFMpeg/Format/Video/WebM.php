<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use FFMpeg\Format\Video\WebM as DefaultVideo;

class WebM extends DefaultVideo
{
    public function getAvailableAudioCodecs()
    {
        return ['copy', 'libvorbis', 'libopus'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableVideoCodecs()
    {
        return ['copy', 'libvpx', 'libvpx-vp9', 'vp8', 'vp9'];
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
        return ['-f', 'webm'];
    }

    /**
     * {@inheritDoc}
     */
    public function getModulus()
    {
        return 2;
    }
}
