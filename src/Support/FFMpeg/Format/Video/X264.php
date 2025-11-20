<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use Domain\Playlists\Models\Playlist;
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
        $frameInterval = $this->getFrameInterval();

        $segmentLength = $this->getSegmentLength();

        return [
            '-preset', 'veryfast',
            '-profile:v', 'main',
            '-x264-params', "keyint={$frameInterval}:min-keyint={$frameInterval}:scenecut=0:profile=main:level=4.1",
            '-keyint_min', (string) $frameInterval,
            '-force_key_frames', "expr:gte(t,n_forced*{$segmentLength})",
            '-sc_threshold', '0',
            '-pix_fmt', 'yuv420p',
        ];
    }

    protected function getFrameInterval(): int
    {
        return Playlist::getFrameInterval();
    }

    protected function getSegmentLength(): int
    {
        return Playlist::getSegmentLength();
    }
}
