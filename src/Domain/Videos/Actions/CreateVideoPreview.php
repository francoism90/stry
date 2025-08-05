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
            if (! $video->hasMedia('clips')) {
                return;
            }

            /** @var Media $media */
            $media = $video->getClipCollection()->first();

            $path = "media_{$media->uuid}_preview.mp4";

            $paths = app(CreateMediaSegments::class)->handle($media);

            FFMpeg::fromDisk('cache')
                ->open($paths)
                ->export()
                ->inFormat(new X264)
                ->concatWithTranscoding(hasAudio: false)
                ->toDisk('cache')
                ->save($path);

            $video
                ->addMediaFromDisk($path, 'cache')
                ->toMediaCollection('previews')
                ->saveOrFail();

            // Clean up temporary files
            collect($paths)->each(fn (string $path) => Storage::disk('cache')->delete($path));

            // Create HLS playlist
            app(CreateVideoPlaylist::class)->handle($video, PlaylistType::Previews);

            return $next($video);
        });
    }
}
