<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Closure;
use Domain\Media\Actions\CreateMediaSegments;
use Domain\Media\Models\Media;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Support\FFMpeg\Format\Video\X264;

class CreateVideoPreview
{
    public function handle(Video $video, Closure $next): mixed
    {
        return DB::transaction(function () use ($video, $next) {
            if ($video->hasPlaylists(PlaylistType::Preview) || ! $video->hasMedia('clips')) {
                return;
            }

            /** @var Media $media */
            $media = $video->getClipCollection()->first();

            $paths = app(CreateMediaSegments::class)->handle($media);

            FFMpeg::fromDisk('cache')
                ->open($paths)
                ->export()
                ->inFormat((new X264)->setKiloBitrate(1500))
                ->concatWithTranscoding(hasAudio: false)
                ->toDisk('cache')
                ->save('preview.mp4');

            $video
                ->addMediaFromDisk('preview.mp4', 'cache')
                ->toMediaCollection('previews')
                ->saveOrFail();

            // Clean up temporary files
            collect($paths)->each(fn (string $path) => Storage::disk('cache')->delete($path));

            // Create HLS playlist
            app(CreateVideoPlaylist::class)->handle($video, PlaylistType::Preview);

            return $next($video);
        });
    }
}
