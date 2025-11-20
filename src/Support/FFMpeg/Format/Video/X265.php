<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

class X265 extends X264
{
    /** @var int */
    private $passes = 1;

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

    public function getHlsParameters(): array
    {
        $frameInterval = $this->getFrameInterval();

        $segmentLength = $this->getSegmentLength();

        return [
            '-preset', 'fast',
            '-profile:v', 'main',
            '-x265-params', "keyint={$frameInterval}:min-keyint={$frameInterval}:scenecut=0:profile=main:level-idc=4.1",
            '-keyint_min', (string) $frameInterval,
            '-force_key_frames', "expr:gte(t,n_forced*{$segmentLength})",
            '-pix_fmt', 'yuv420p',
        ];
    }
}
