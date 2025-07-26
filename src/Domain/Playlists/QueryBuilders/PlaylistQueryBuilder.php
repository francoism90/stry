<?php

declare(strict_types=1);

namespace Domain\Playlists\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class PlaylistQueryBuilder extends Builder
{
    public function pending(): self
    {
        return $this
            ->whereNull('transcoded_at');
    }

    public function transcoded(): self
    {
        return $this
            ->whereNotNull('transcoded_at');
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
            ->transcoded()
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
