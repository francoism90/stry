<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Domain\Media\Models\Media;

/**
 * A short vertical highlight video of the best scenes, kept in the "reels" collection and shown in the reels feed.
 * Short videos without a reel can show in the feed too, playing their clip.
 */
trait InteractsWithReels
{
    public function hasReel(): bool
    {
        return $this->hasMedia('reels');
    }

    public function getReel(): ?Media
    {
        return $this->getFirstMedia('reels');
    }

    public function getReelUrl(): ?string
    {
        return $this->temporaryMediaUrl($this->getReel() ?? $this->getClips()->first());
    }
}
