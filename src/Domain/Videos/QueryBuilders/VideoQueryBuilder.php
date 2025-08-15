<?php

declare(strict_types=1);

namespace Domain\Videos\QueryBuilders;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\States\Verified;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;

class VideoQueryBuilder extends Builder
{
    public function published(): self
    {
        return $this
            ->verified()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function verified(): self
    {
        return $this->whereState('state', Verified::class);
    }

    public function recent(): self
    {
        return $this
            ->orderByDesc('created_at')
            ->orderByDesc('released_at');
    }

    public function watching(): self
    {
        return $this
            ->whereHas('groups', fn (Builder $query) => $query
                ->where('user_id', Auth::id())
                ->where('type', GroupType::Viewed)
            )
            ->join('groupables', fn (JoinClause $join) => $join
                ->on('videos.id', '=', 'groupables.groupable_id')
                ->where('groupables.groupable_type', 'video')
            )
            ->orderByDesc('groupables.updated_at');
    }
}
