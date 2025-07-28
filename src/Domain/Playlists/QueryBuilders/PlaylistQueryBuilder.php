<?php

declare(strict_types=1);

namespace Domain\Playlists\QueryBuilders;

use Domain\Playlists\States\Pending;
use Domain\Playlists\States\Verified;
use Illuminate\Database\Eloquent\Builder;

class PlaylistQueryBuilder extends Builder
{
    public function pending(): self
    {
        return $this->whereState('state', Pending::class);
    }

    public function verified(): self
    {
        return $this->whereState('state', Verified::class);
    }

    public function expired(): self
    {
        return $this
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('expires_at')
            ->orderBy('created_at');
    }

    public function active(): self
    {
        return $this
            ->verified()
            ->whereNot(fn ($query) => $query->expired())
            ->ordered();
    }

    public function ordered(): self
    {
        return $this
            ->orderByDesc('expires_at')
            ->orderByDesc('transcoded_at')
            ->latest();
    }
}
