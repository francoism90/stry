<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Media\Models\Media;
use Domain\Videos\Settings\PlaybackSettings;
use Foxws\Media\Encoding\Ladder;
use Foxws\Media\Encoding\Rendition;
use Foxws\Media\MediaFactory;
use Illuminate\Support\Facades\Storage;

/**
 * Encodes the renditions chosen in the playback settings that are smaller than the clip, with
 * keyframes aligned to it, so direct play streams the untouched clip as the top variant and the
 * renditions below it. Renditions that are no longer wanted are deleted.
 */
class CreateClipRenditions
{
    public function __construct(
        protected MediaFactory $media,
        protected PlaybackSettings $settings,
    ) {}

    public function handle(Media $clip): void
    {
        $heights = $this->settings->renditions;
        $opener = $this->media->fromDisk($clip->disk)->open($clip->getPathRelativeToRoot());
        $source = $opener->probe()->videoStream();
        $shortSide = $source !== null ? min($source->width ?? 0, $source->height ?? 0) : 0;

        $renditions = array_values(array_filter(
            Ladder::standard()->renditions,
            fn (Rendition $rendition): bool => in_array($rendition->height, $heights, true) && $rendition->height < $shortSide,
        ));

        $previous = $clip->getRenditionPaths();
        $paths = [];

        try {
            if ($renditions !== []) {
                $paths = $opener
                    ->ladder(new Ladder($renditions)->alignToSource(), $clip->getRenditionPathPattern())
                    ->save()
                    ->paths();
            }
        } finally {
            $opener->cleanupTemporaryFiles();
        }

        $clip->saveRenditions($heights, $paths);

        $unused = array_values(array_diff($previous, $paths));

        if ($unused !== []) {
            Storage::disk($clip->disk)->delete($unused);
        }
    }
}
