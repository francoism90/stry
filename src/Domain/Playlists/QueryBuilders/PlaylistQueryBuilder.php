<?php

declare(strict_types=1);

namespace Domain\Playlists\QueryBuilders;

use ArrayAccess;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\States\Failed;
use Domain\Playlists\States\Pending;
use Domain\Playlists\States\Verified;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class PlaylistQueryBuilder extends Builder
{
    public function failed(): self
    {
        return $this->whereState('state', Failed::class);
    }

    public function pending(): self
    {
        return $this->whereState('state', Pending::class);
    }

    public function verified(): self
    {
        return $this->whereState('state', Verified::class);
    }

    public function active(): self
    {
        return $this
            ->whereNot(fn ($query) => $query->expired())
            ->ordered();
    }

    public function type(ArrayAccess|array|PlaylistType $type): self
    {
        return $this->whereIn('type', Arr::wrap($type));
    }

    public function expired(): self
    {
        return $this
            ->whereNotNull('expires_at')
            ->whereNowOrPast('expires_at');
    }

    public function ordered(): self
    {
        return $this
            ->orderByDesc('expires_at')
            ->orderByDesc('transcoded_at')
            ->latest();
    }
}
