<?php

declare(strict_types=1);

namespace Domain\Videos\QueryBuilders;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
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

    public function likedBy(?User $user = null): self
    {
        return $this->inGroup($user, GroupType::Liked);
    }

    public function savedBy(?User $user = null): self
    {
        return $this->inGroup($user, GroupType::Saved);
    }

    public function viewedBy(?User $user = null): self
    {
        return $this->inGroup($user, GroupType::Viewed);
    }

    public function inGroup(?User $user, GroupType $type): self
    {
        if (! $user) {
            return $this;
        }

        return $this
            ->select('videos.*')
            ->distinct()
            ->join('groupables', 'videos.id', '=', 'groupables.groupable_id')
            ->join('groups', 'groupables.group_id', '=', 'groups.id')
            ->where('groupables.groupable_type', 'video')
            ->whereNotNull('groupables.group_id')
            ->where('groups.type', $type->value)
            ->where('groups.user_id', $user->getKey())
            ->orderByDesc('groupables.created_at');
    }
}
