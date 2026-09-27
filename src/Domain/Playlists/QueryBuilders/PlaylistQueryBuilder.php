<?php

declare(strict_types=1);

namespace Domain\Playlists\QueryBuilders;

use ArrayAccess;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Domain\Playlists\States;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * @template TModel of Playlist
 *
 * @extends Builder<TModel>
 */
class PlaylistQueryBuilder extends Builder
{
    /**
     * @param  PlaylistType|ArrayAccess<array-key, PlaylistType|string>|array<array-key, PlaylistType|string>|null  $type
     * @return self<TModel>
     */
    public function type(PlaylistType|ArrayAccess|array|null $type = null): self
    {
        return $this->when($type, fn ($query) => $query->whereIn('type', Arr::wrap($type)));
    }

    /** @return self<TModel> */
    public function failed(): self
    {
        return $this->whereState('state', States\Failed::class);
    }

    /** @return self<TModel> */
    public function pending(): self
    {
        return $this->whereState('state', States\Pending::class);
    }

    /** @return self<TModel> */
    public function verified(): self
    {
        return $this->whereState('state', States\Verified::class);
    }

    /** @return self<TModel> */
    public function expired(): self
    {
        return $this->where(fn ($query) => $query
            ->whereNotNull('expires_at')
            ->whereNowOrPast('expires_at')
        );
    }

    /** @return self<TModel> */
    public function ordered(): self
    {
        return $this
            ->orderByDesc('expires_at')
            ->orderByDesc('transcoded_at')
            ->latest();
    }

    /** @return self<TModel> */
    public function prunable(): self
    {
        return $this
            ->expired()
            ->orWhere(fn ($query) => $query->failed())
            ->oldest();
    }

    /** @return self<TModel> */
    public function current(): self
    {
        return $this
            ->whereNot(fn ($query) => $query->expired())
            ->ordered();
    }
}
