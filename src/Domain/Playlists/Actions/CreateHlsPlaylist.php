<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Models\Playlist;
use FFMpeg\Format\Video\DefaultVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\CopyVideoFormat;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\X264;

class CreateHlsPlaylist
{
    public function handle(Playlist $playlist, string $disk, string $path): Playlist
    {
        return DB::transaction(function () use ($playlist, $disk, $path) {
            // Make sure there is enough space available on the disk
            app(CheckAvailableSpace::class)->handle($playlist);

            // Initialize the FFMpeg exporter
            $ffmpeg = FFMpeg::fromDisk($disk)
                ->open($path)
                ->exportForHLS()
                ->toDisk($playlist->getDisk())
                ->setSegmentLength(Playlist::getSegmentLength())
                ->setKeyFrameInterval(Playlist::getFrameInterval());

            // Use rotation key if specified
            if (Playlist::shouldUseRotationKeys()) {
                $secrets = $playlist->getSecretFilesystem();
                $segmentsPerKey = Playlist::getRotationKeysSections();

                $ffmpeg->withRotatingEncryptionKey(fn (string $filename, string $contents) => $secrets->put($playlist->getPath($filename), $contents), $segmentsPerKey);
            }

            // Determine the suitable video format for the source file
            $video = app(GetSuitableVideoFormat::class)->handle($disk, $path);

            // Add formats to the ffmpeg exporter
            Playlist::getHlsFormats()->each(function (Fluent $preset) use (&$ffmpeg, $video) {
                /** @var DefaultVideo $format */
                $format = $preset->get('format', $video->get('format', X264::class));

                $videoCodec = $video->get('copy_video', false) ? 'copy' : $preset->get('video_codec', $format->getVideoCodec());
                $audioCodec = $video->get('copy_audio', false) ? 'copy' : $preset->get('audio_codec', $format->getAudioCodec());

                $videoBitrate = $preset->get('kilo_bitrate', $format->getKiloBitrate());
                $audioBitrate = $preset->get('audio_bitrate', $format->getAudioKiloBitrate());

                // If both audio and video codecs are set to copy, use the CopyVideoFormat
                if ($videoCodec === 'copy' && $audioCodec === 'copy') {
                    $ffmpeg->addFormat((new CopyVideoFormat)->setAdditionalParameters(
                        Playlist::getAdditionalParameters(),
                    ));

                    return;
                }

                // Add the format with the specified codecs and bitrate
                $ffmpeg->addFormat($format
                    ->setVideoCodec($videoCodec)
                    ->setAudioCodec($audioCodec)
                    ->setKiloBitrate($videoCodec === 'copy' ? 0 : $videoBitrate)
                    ->setAudioKiloBitrate($audioBitrate)
                    ->setAdditionalParameters(Playlist::getAdditionalParameters()),
                );
            });

            // Monitor progress of the transcoding
            $ffmpeg->onProgress(fn (int|float $percentage, int|float $remaining, int|float $rate) => $playlist->updateQuietly([
                'progress' => compact('percentage', 'remaining', 'rate'),
            ]));

            // Run the transcoding process
            $ffmpeg
                ->save($playlist->getPath($playlist->file_name))
                ->cleanupTemporaryFiles();

            // Mark the playlist as processed
            app(MarkPlaylistAsProcessed::class)->handle($playlist);

            return $playlist;
        });
    }
}
