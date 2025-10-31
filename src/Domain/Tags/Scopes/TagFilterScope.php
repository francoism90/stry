<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Laravel\Scout\Builder;

class TagFilterScope
{
    public function __construct(
        protected TagType|string|null $filter = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->query(fn (TagQueryBuilder $scout) => $scout->withCount('videos'))
            ->when($this->hasFilter(), fn (Builder $scout) => $scout->where('type', $this->getFilter()->value)->orderBy('name'))
            ->unless($this->hasFilter() && filled($scout->query), fn ($scout) => $scout->orderBy('_rand()'));
    }

    protected function hasFilter(): bool
    {
        return filled($this->getFilter());
    }

    protected function isFilter(TagType $value): bool
    {
        return $this->getFilter() === $value;
    }

    protected function getFilter(): ?TagType
    {
        if (! $this->filter) {
            return null;
        }

        return TagType::tryFrom($this->filter);
    }
}
