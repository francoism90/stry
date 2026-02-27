<?php

declare(strict_types=1);

namespace Domain\Playlists\QueryBuilders;

use ArrayAccess;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\States;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class PlaylistQueryBuilder extends Builder
{
    public function type(PlaylistType|ArrayAccess|array|null $type = null): self
    {
        return $this->when($type, fn ($query) => $query->whereIn('type', Arr::wrap($type)));
    }

    public function failed(): self
    {
        return $this->whereState('state', States\Failed::class);
    }

    public function pending(): self
    {
        return $this->whereState('state', States\Pending::class);
    }

    public function verified(): self
    {
        return $this->whereState('state', States\Verified::class);
    }

    public function expired(): self
    {
        return $this->where(fn ($query) => $query
            ->where(fn ($q) => $q
                ->whereNotNull('expires_at')
                ->whereNowOrPast('expires_at')
            )
            ->orWhere(fn ($q) => $q->failed())
        );
    }

    public function ordered(): self
    {
        return $this
            ->orderByDesc('expires_at')
            ->orderByDesc('transcoded_at')
            ->latest();
    }
}
