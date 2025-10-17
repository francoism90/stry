<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use FFMpeg\Format\Video\DefaultVideo;
use Illuminate\Support\Fluent;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class GetSuitableVideoFormat
{
    public function handle(string $disk, string $path): Fluent
    {
        $ffmpeg = FFMpeg::fromDisk($disk)->open($path);

        // Detect the video and audio codecs of the source file
        $videoCodec = $ffmpeg->getVideoStream()?->get('codec_name');
        $audioCodec = $ffmpeg->getAudioStream()?->get('codec_name');

        // Find the best suitable format based on the given formats and source codecs
        // If no matching codec is found, default to first given format in list.
        $formats = Playlist::getVideoFormats();

        $format = $formats->first(
            fn (DefaultVideo $videoFormat) => $videoCodec !== null
                && in_array($audioCodec, $videoFormat->getAvailableAudioCodecs())
                && in_array($videoCodec, $videoFormat->getAvailableVideoCodecs()),
            fn () => $formats->first()
        );

        // Determine if we can copy the audio and video codecs
        $copyAudioFormat = (Playlist::copyAudioCodec() && in_array($audioCodec, $format->getAvailableAudioCodecs()));
        $copyVideoFormat = (Playlist::copyVideoCodec() && in_array($videoCodec, $format->getAvailableVideoCodecs()));

        return Fluent::make([
            'format' => $format,
            'video_codec' => $videoCodec,
            'audio_codec' => $audioCodec,
            'copy_audio' => $copyAudioFormat,
            'copy_video' => $copyVideoFormat,
        ]);
    }
}
