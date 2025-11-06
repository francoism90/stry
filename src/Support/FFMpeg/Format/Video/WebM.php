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
        return ['copy', 'libvpx', 'libvpx-vp9', 'vp8', 'vp9', 'libaom-av1', 'libsvtav1', 'av1'];
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
            '-f',
            'webm',
            '-quality',
            'good',
            '-cpu-used',
            '2',
            '-crf',
            '28',
            '-b:v',
            '0',
            '-row-mt',
            '1',
            '-tile-columns',
            '2',
            '-tile-rows',
            '1',
            '-sc_threshold',
            '0',
            '-pix_fmt',
            'yuv420p',
        ];
    }
}
