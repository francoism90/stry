<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;
use Domain\Media\Models\Media;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\ProcessingSettings;
use Foxws\Media\Exceptions\InvalidMediaException;
use Foxws\Media\FFMpeg\ThumbnailsResult;
use Foxws\Media\MediaFactory;

/**
 * Samples the best clip into thumbnail sprite sheets, kept on the clip, which direct play offers as image tracks
 * for seek previews. Long clips decode only their keyframes, which is much faster; short ones decode every frame,
 * so their thumbnails don't repeat the same keyframe.
 */
class ExtractVideoThumbnails
{
    /**
     * Clips longer than this many seconds decode only their keyframes.
     */
    public const int KEYFRAMES_ONLY_AFTER = 120;

    public function __construct(
        private readonly ProcessingSettings $settings,
        private readonly MediaFactory $media,
    ) {}

    public function handle(Video $video, Closure $next): mixed
    {
        $clip = $video->getClips()->first();

        if (! $this->settings->extract_storyboard || $clip === null || $clip->hasThumbnails()) {
            return $next($video);
        }

        $this->extract($clip);

        return $next($video);
    }

    /**
     * Sample the clip and keep the result on it, replacing its previous thumbnails. Sheets the new
     * result doesn't reuse are deleted afterwards, so the clip always has a complete set.
     */
    public function extract(Media $clip): ?ThumbnailsResult
    {
        $previous = $clip->getThumbnails();
        $opener = $this->media->fromDisk($clip->disk)->open($clip->getPathRelativeToRoot());

        try {
            $builder = $opener->thumbnails()->count(300, minimumInterval: 1);

            if ($opener->probe()->duration() > self::KEYFRAMES_ONLY_AFTER) {
                $builder->keyframesOnly();
            }

            $thumbnails = $builder->toDisk($clip::getThumbnailsDisk())->save("{$clip->uuid}/thumbnails");
        } catch (InvalidMediaException) {
            return null;
        }

        $clip->saveThumbnails($thumbnails);

        if ($previous !== null) {
            $previous->disk->filesystem()->delete(array_values(array_diff($previous->paths(), $thumbnails->paths())));
        }

        return $thumbnails;
    }
}
