<?php

declare(strict_types=1);

namespace Domain\Videos\QueryBuilders;

use Domain\Profiles\Models\Profile;
use Domain\Videos\States;
use Illuminate\Database\Eloquent\Builder;

class VideoQueryBuilder extends Builder
{
    public function failed(): self
    {
        return $this->whereState('state', States\Failed::class);
    }

    public function verified(): self
    {
        return $this->whereState('state', States\Verified::class);
    }

    public function published(): self
    {
        return $this
            ->verified()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function recent(): self
    {
        return $this
            ->orderByDesc('released_at')
            ->orderByDesc('published_at')
            ->latest();
    }

    public function forProfile(?Profile $profile = null): self
    {
        if ($profile?->isKids()) {
            return $this->where('adult', false);
        }

        return $this;
    }
}
