<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Videos\Models\Video;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Support\FFMpeg\Format\Video\WebVTT;

class CreateVideoCaptions
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            if (! $video->hasCaptions()) {
                return $next($video);
            }

            // Get the first media item from the video
            $media = $video->getClipCollection()->first();

            // Initialize FFMpeg
            $ffmpeg = FFMpeg::fromDisk($media->disk)->open($media->getPathRelativeToRoot());

            $temporaryDirectory = TemporaryDirectory::make('transcodes');

            Collection::make($ffmpeg->getStreams())
                ->filter(fn (Stream $stream) => $stream->get('codec_type') === 'subtitle')
                ->each(function (Stream $stream) use ($temporaryDirectory, $ffmpeg, $video) {
                    $index = $stream->get('index', 0);
                    $language = data_get($stream->get('tags', []), 'language', 'und');
                    $path = $temporaryDirectory->path("{$index}-{$language}.vtt");

                    // Export the caption stream to a WebVTT file
                    $ffmpeg
                        ->export()
                        ->toDisk('transcodes')
                        ->inFormat(new WebVTT)
                        ->addFilter(['-map', "0:{$index}"])
                        ->save($path);

                    // Add the caption file to the video
                    $video
                        ->addMediaFromDisk($path, 'transcodes')
                        ->toMediaCollection('captions')
                        ->saveOrFail();
                });

            // Clean up temporary files
            $temporaryDirectory->delete();

            return $next($video);
        });
    }
}
