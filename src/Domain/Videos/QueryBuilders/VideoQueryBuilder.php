<?php

declare(strict_types=1);

namespace Domain\Videos\QueryBuilders;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\States\Failed;
use Domain\Videos\States\Verified;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;

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
            ->orderByDesc('created_at');
    }

    public function watching(): self
    {
        return $this
            ->whereHas('groups', fn (Builder $query) => $query
                ->where('user_id', Auth::id())
                ->where('type', GroupType::Viewed),
            )
            ->join('groupables', fn (JoinClause $join) => $join
                ->on('videos.id', '=', 'groupables.groupable_id')
                ->where('groupables.groupable_type', 'video'),
            )
            ->orderByDesc('groupables.updated_at');
    }
}
