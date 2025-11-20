<?php

declare(strict_types=1);

namespace Support\FFMpeg\Format\Video;

use Domain\Playlists\Models\Playlist;
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
        return ['copy', 'libaom-av1', 'libsvtav1', 'librav1e', 'av1'];
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
            '-cpu-used', '4',
            '-threads', '8',
            '-lag-in-frames', '0',
            '-row-mt', '1',
            '-tile-columns', '0',
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
