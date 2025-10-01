<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Laravel\Scout\Builder;

class TagSearchScope
{
    public function __construct(
        protected TagType|string|null $type = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->query(fn (TagQueryBuilder $query) => $query->withCount('videos'))
            ->when($this->type, fn (Builder $query, TagType|string $type) => $query->where('type', $type))
            ->unless($this->type || $query->query, fn (Builder $query) => $query->orderByDesc('videos'));
    }
}
