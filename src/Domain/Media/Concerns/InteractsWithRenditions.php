<?php

declare(strict_types=1);

namespace Domain\Media\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Smaller renditions encoded from this clip, stored next to it on its disk and kept in its
 * "renditions" custom property. Their keyframes are aligned to the clip, so direct play streams
 * them as variants of the clip.
 */
trait InteractsWithRenditions
{
    public static function bootInteractsWithRenditions(): void
    {
        static::deleted(fn (self $model) => $model->deleteRenditions());
    }

    /**
     * The heights the renditions were encoded for, as asked, including those skipped because the
     * clip isn't larger.
     *
     * @return list<int>
     */
    public function getRenditionHeights(): array
    {
        /** @var list<int> */
        return array_values(array_map(intval(...), (array) $this->getCustomProperty('renditions.heights', [])));
    }

    /**
     * The paths of the renditions on the clip's disk, largest first.
     *
     * @return list<string>
     */
    public function getRenditionPaths(): array
    {
        /** @var list<string> */
        return array_values(array_map(strval(...), (array) $this->getCustomProperty('renditions.paths', [])));
    }

    /**
     * Where the renditions are stored, next to the clip, with {height} for the height.
     */
    public function getRenditionPathPattern(): string
    {
        return dirname($this->getPathRelativeToRoot()).'/renditions/{height}p.mp4';
    }

    /**
     * @param  list<int>  $heights
     * @param  list<string>  $paths
     */
    public function saveRenditions(array $heights, array $paths): void
    {
        $this->setCustomProperty('renditions', ['heights' => $heights, 'paths' => $paths])->saveQuietly();
    }

    public function deleteRenditions(): void
    {
        $paths = $this->getRenditionPaths();

        if ($paths !== []) {
            Storage::disk($this->disk)->delete($paths);
        }
    }
}
