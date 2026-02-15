<?php

declare(strict_types=1);

namespace Domain\Playlists\Scopes;

use Domain\Playlists\Enums\PlaylistType;
use Illuminate\Database\Eloquent\Builder;

readonly class PlaylistFilterScope
{
    public function __construct(
        public PlaylistType|string|null $type = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->when($this->getType(), fn (Builder $query, PlaylistType $type) => $query->type($type))
            ->latest();
    }

    protected function getType(): ?PlaylistType
    {
        $typeValue = $this->type ?? null;

        return is_string($typeValue) ? PlaylistType::tryFrom($typeValue) : $typeValue;
    }
}
