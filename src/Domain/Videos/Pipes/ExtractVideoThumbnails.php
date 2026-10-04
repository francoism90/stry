<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\ProcessingSettings;
use Foxws\Media\Exceptions\InvalidMediaException;
use Foxws\Media\MediaFactory;

/**
 * Samples the best clip into thumbnail sprite sheets, which direct play offers as image tracks
 * for seek previews. Only keyframes are decoded, so it's fast even for long videos.
 */
class ExtractVideoThumbnails
{
    public function __construct(
        private readonly ProcessingSettings $settings,
        private readonly MediaFactory $media,
    ) {}

    public function handle(Video $video, Closure $next): mixed
    {
        if (! $this->settings->extract_storyboard || filled($video->thumbnails) || ! $video->hasMedia('clips')) {
            return $next($video);
        }

        $clip = $video->getClips()->firstOrFail();

        try {
            $thumbnails = $this->media->fromDisk($clip->disk)
                ->open($clip->getPathRelativeToRoot())
                ->thumbnails()
                ->count(300, minimumInterval: 5)
                ->keyframesOnly()
                ->toDisk(Video::getThumbnailsDisk())
                ->save("{$video->getKey()}/thumbnails");
        } catch (InvalidMediaException) {
            return $next($video);
        }

        $video->forceFill(['thumbnails' => $thumbnails->toArray()])->saveQuietly();

        return $next($video);
    }
}
