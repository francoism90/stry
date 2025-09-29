<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagType;
use Laravel\Scout\Builder;

class TagSearchScope
{
    public function __construct(
        protected TagType|string|null $type = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->unless($query->query, fn (Builder $query) => $query->orderByDesc('videos'))
            ->when($this->type, fn (Builder $query, TagType|string $type) => $query->where('type', $type));
    }
}
