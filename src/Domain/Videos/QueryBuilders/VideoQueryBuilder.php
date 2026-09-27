<?php

declare(strict_types=1);

namespace Domain\Videos\QueryBuilders;

use Domain\Profiles\Models\Profile;
use Domain\Videos\Models\Video;
use Domain\Videos\States;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Video
 *
 * @extends Builder<TModel>
 */
class VideoQueryBuilder extends Builder
{
    /** @return self<TModel> */
    public function failed(): self
    {
        return $this->whereState('state', States\Failed::class);
    }

    /** @return self<TModel> */
    public function verified(): self
    {
        return $this->whereState('state', States\Verified::class);
    }

    /** @return self<TModel> */
    public function published(): self
    {
        return $this
            ->verified()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @return self<TModel> */
    public function recent(): self
    {
        return $this
            ->orderByDesc('released_at')
            ->orderByDesc('published_at')
            ->latest();
    }

    /** @return self<TModel> */
    public function forProfile(?Profile $profile = null): self
    {
        if ($profile?->isKids()) {
            return $this->where('adult', false);
        }

        return $this;
    }
}
