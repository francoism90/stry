<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Laravel\Scout\Builder;

use function Illuminate\Support\enum_value;

class TagFilterScope
{
    public function __construct(
        protected TagType|string|null $filter = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->query(fn (TagQueryBuilder $scout) => $scout->withCount('videos'))
            ->when(blank($scout->query), fn (Builder $scout) => $scout->orderByDesc('videos'))
            ->when($this->getFilter(), fn (Builder $scout, TagType $type) => $scout->where('type', enum_value($type))->orderBy('name'));
    }

    protected function getFilter(): ?TagType
    {
        if (! $this->filter) {
            return null;
        }

        return $this->filter instanceof TagType
            ? $this->filter
            : TagType::tryFrom($this->filter);
    }

    protected function isFilter(TagType ...$values): bool
    {
        $filterValue = $this->getFilter();

        return $filterValue && in_array($filterValue, $values, true);
    }

    protected function hasFilter(): bool
    {
        return filled($this->getFilter());
    }
}
