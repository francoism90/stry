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

    public function favoriteBy(?User $user = null): self
    {
        return $this->inGroup($user, GroupType::Favorited);
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

        $table = $this->getModel()->getTable();

        $morph = $this->getModel()->getMorphClass();

        return $this->whereExists(function ($query) use ($user, $type, $table, $morph) {
            $query
                ->selectRaw('1')
                ->from('groupables')
                ->join('groups', 'groups.id', '=', 'groupables.group_id')
                ->whereColumn('groupables.groupable_id', $table . '.id')
                ->where('groupables.groupable_type', $morph)
                ->where('groups.user_id', $user->getKey())
                ->where('groups.type', $type);
        });
    }
}
