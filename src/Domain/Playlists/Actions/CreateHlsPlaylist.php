<?php

declare(strict_types=1);

namespace Domain\Playlists\Actions;

use Domain\Playlists\Jobs\PlaylistProgress;
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
            // Initialize ffmpeg exporter
            $ffmpeg = FFMpeg::fromDisk($disk)
                ->open($path)
                ->exportForHLS()
                ->withoutPlaylistEndLine()
                ->toDisk($playlist->getDisk())
                ->setSegmentLength(Playlist::getSegmentLength())
                ->setKeyFrameInterval(Playlist::getFrameInterval());

            // Use rotation key if specified
            if (Playlist::useRotationKeys()) {
                $secrets = $playlist->getSecretFilesystem();
                $segmentsPerKey = Playlist::getRotationKeysSections();

                $ffmpeg->withRotatingEncryptionKey(fn (string $filename, string $contents) => $secrets->put($playlist->getPath($filename), $contents), $segmentsPerKey);
            }

            // Monitor progress of the transcoding
            $ffmpeg->onProgress(fn (?float $percentage = null, ?float $remaining = null, ?float $rate = null) => PlaylistProgress::dispatch(
                playlist: $playlist,
                attributes: compact('percentage', 'remaining', 'rate'),
            ));

            // Find the video format for the given media file
            $video = app(GetVideoFormat::class)->handle($disk, $path);

            // Add formats to the ffmpeg exporter
            Playlist::getHlsFormats()->each(function (Fluent $preset) use ($ffmpeg, $video) {
                // If prevent-transcoding is requested and both codecs can be copied
                if ($video->value('copy_format')) {
                    $ffmpeg->addFormat(new CopyVideoFormat);

                    return;
                }

                /** @var DefaultVideo $format */
                $format = $preset->value('format', $video->value('format', new X264));

                $ffmpeg->addFormat(
                    $format
                        ->setVideoCodec($video->value('copy_video') ? 'copy' : $preset->value('video_codec', $format->getVideoCodec()))
                        ->setAudioCodec($video->value('copy_audio') ? 'copy' : $preset->value('audio_codec', $format->getAudioCodec()))
                        ->setKiloBitrate($preset->value('kilo_bitrate', $format->getKiloBitrate()))
                );
            });

            // Run the transcoding process
            $ffmpeg->save($playlist->getPath($playlist->file_name));

            // Mark the playlist as transcoded
            $playlist->touch('transcoded_at');

            return $playlist;
        });
    }
}
