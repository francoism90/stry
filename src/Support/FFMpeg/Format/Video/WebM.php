<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use Domain\Playlists\Models\Playlist;
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

    public function getPlaylistParameters(): array
    {
        $frameInterval = $this->getFrameInterval();

        $segmentLength = $this->getSegmentLength();

        return [
            '-deadline', 'realtime',
            '-cpu-used', '4',
            '-g', (string) $frameInterval,
            '-keyint_min', (string) $frameInterval,
            '-pix_fmt', 'yuv420p',
            '-force_key_frames', "expr:gte(t,n_forced*{$segmentLength})",
        ];
    }

    public function getFrameInterval(): int
    {
        return Playlist::getFrameInterval();
    }

    public function getSegmentLength(): int
    {
        return Playlist::getSegmentLength();
    }
}
