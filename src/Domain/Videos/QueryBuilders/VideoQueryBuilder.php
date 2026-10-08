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
    /**
     * A prime above any video ID, so ordering by (id × seed) mod it shuffles videos without
     * database-specific functions.
     */
    public const int SHUFFLE_MODULUS = 1000003;

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

    /**
     * Videos that have at least one clip, so they have a thumbnail.
     *
     * @return self<TModel>
     */
    public function withClips(): self
    {
        return $this->whereHas('media', fn (Builder $query) => $query->where('collection_name', 'clips'));
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

    /**
     * Order videos in a shuffled order that's the same for the same seed (1 to SHUFFLE_MODULUS - 1).
     *
     * @return self<TModel>
     */
    public function shuffled(int $seed): self
    {
        return $this->orderByRaw('(id * ?) % ?', [$seed, self::SHUFFLE_MODULUS]);
    }
}
