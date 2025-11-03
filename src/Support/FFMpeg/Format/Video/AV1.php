<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use FFMpeg\Format\Video\DefaultVideo;

class AV1 extends DefaultVideo
{
    public function __construct($audioCodec = 'libopus', $videoCodec = 'libaom-av1')
    {
        $this
            ->setAudioCodec($audioCodec)
            ->setVideoCodec($videoCodec);
    }

    public function getAvailableAudioCodecs()
    {
        return ['copy', 'aac', 'libopus', 'libvorbis', 'libfdk_aac'];
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableVideoCodecs()
    {
        return ['copy', 'libaom-av1', 'libsvtav1', 'av1'];
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
            '-cpu-used', '4',
            '-row-mt', '1',
            '-tiles', '2x2',
            '-strict', 'experimental',
        ];
    }
}
