<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Jobs\SyncProgress;
use Domain\Playlists\Models\Playlist;
use FFMpeg\Format\Video\DefaultVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use ProtoneMedia\LaravelFFMpeg\FFMpeg\CopyVideoFormat;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

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
                ->withoutPlaylistEndLine()
                ->toDisk($playlist->getDisk())
                ->setSegmentLength(Playlist::getSegmentLength())
                ->setKeyFrameInterval(Playlist::getFrameInterval());

            // Use rotation key if specified
            if (Playlist::shouldUseRotationKeys()) {
                $secrets = $playlist->getSecretFilesystem();
                $segmentsPerKey = Playlist::getRotationKeysSections();

                $ffmpeg->withRotatingEncryptionKey(fn (string $filename, string $contents) => $secrets->put($playlist->getPath($filename), $contents), $segmentsPerKey);
            }

            // Monitor progress of the transcoding
            $ffmpeg->onProgress(fn (?float $percentage = null, ?float $remaining = null, ?float $rate = null) => SyncProgress::dispatch(
                playlist: $playlist,
                attributes: compact('percentage', 'remaining', 'rate'),
            ));

            // Find the video format for the given media file
            $video = app(GetVideoFormat::class)->handle($disk, $path);

            // Add formats to the ffmpeg exporter
            Playlist::getHlsFormats()->each(function (Fluent $preset) use ($ffmpeg, $video) {
                /** @var DefaultVideo $format */
                $format = $preset->get('format', $video->get('format'));

                $kiloBitrate = $preset->get('kilo_bitrate', $format->getKiloBitrate());

                $videoCodec = $video->get('copy_video') && $kiloBitrate === 0 ? 'copy' : $preset->get('video_codec', $format->getVideoCodec());
                $audioCodec = $video->get('copy_audio') ? 'copy' : $preset->get('audio_codec', $format->getAudioCodec());

                // If bitrate is 0, we assume we want to copy the video and audio codecs
                if ($videoCodec === 'copy' && $audioCodec === 'copy') {
                    $ffmpeg->addFormat((new CopyVideoFormat)->setAdditionalParameters(
                        Playlist::getAdditionalParameters()
                    ));

                    return;
                }

                $ffmpeg->addFormat($format
                    ->setVideoCodec($videoCodec)
                    ->setAudioCodec($audioCodec)
                    ->setKiloBitrate($kiloBitrate)
                    ->setAdditionalParameters(Playlist::getAdditionalParameters())
                );
            });

            // Run the transcoding process
            $ffmpeg->save($playlist->getPath($playlist->file_name));

            // Mark the playlist as processed
            app(MarkPlaylistAsVerified::class)->handle($playlist);

            return $playlist;
        });
    }
}
