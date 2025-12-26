<?php

declare(strict_types=1);

namespace Domain\Videos\QueryBuilders;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\States\Failed;
use Domain\Videos\States\Verified;
use Illuminate\Database\Eloquent\Builder;

class VideoQueryBuilder extends Builder
{
    public function failed(): self
    {
        return $this->whereState('state', Failed::class);
    }

    public function verified(): self
    {
        return $this->whereState('state', Verified::class);
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

    public function favoriteBy(User $user): self
    {
        return $this->byGroupType($user, GroupType::Favorited);
    }

    public function savedBy(User $user): self
    {
        return $this->byGroupType($user, GroupType::Saved);
    }

    public function viewedBy(User $user): self
    {
        return $this->byGroupType($user, GroupType::Viewed);
    }

    public function byGroupType(User $user, GroupType $type): self
    {
        return $this->whereHas('groups', fn (Builder $query) => $query
            ->where('user_id', $user->getKey())
            ->where('type', $type),
        );
    }
}
