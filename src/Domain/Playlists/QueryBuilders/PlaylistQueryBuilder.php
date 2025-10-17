<?php

declare(strict_types=1);

namespace Domain\Playlists\QueryBuilders;

use ArrayAccess;
use Domain\Playlists\Models\Playlist;
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
            ->whereNot(fn ($query) => $query->failed())
            ->whereNot(fn ($query) => $query->expired())
            ->ordered();
    }

    public function type(ArrayAccess|array|string $type): self
    {
        return $this->whereIn('type', Arr::wrap($type));
    }

    public function expired(): self
    {
        return $this
            ->whereNotNull('expires_at')
            ->whereNowOrPast('expires_at');
    }

    public function stale(): self
    {
        $staleAfter = Playlist::getStaleAfter();

        return $this->when($staleAfter > 0, fn ($query) => $query
            ->whereNotNull('accessed_at')
            ->whereNotNull('expires_at')
            ->where('accessed_at', '<=', now()->subSeconds($staleAfter))
            ->orderBy('accessed_at')
            ->oldest()
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
