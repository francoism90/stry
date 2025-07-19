<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use FFMpeg\Format\Video\WebM as DefaultVideo;

class WebM extends DefaultVideo
{
    public function getAvailableAudioCodecs()
    {
        return ['copy', 'libvorbis'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableVideoCodecs()
    {
        return ['copy', 'libvpx', 'libvpx-vp9'];
    }
}
