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
            ->when($this->hasFilter(), fn (Builder $scout) => $scout->where('type', $this->getFilter())->orderBy('name'))
            ->when(! $this->hasFilter() && blank($scout->query), fn (Builder $scout) => $scout->orderByDesc('videos'))
            ->unless($this->hasFilter() && filled($scout->query), fn ($scout) => $scout->orderBy('_rand()'));
    }

    protected function hasFilter(): bool
    {
        if ($this->filter === 'all') {
            return false;
        }

        return filled($this->getFilter());
    }

    protected function getFilter(): ?string
    {
        if (! $this->filter) {
            return enum_value(TagType::Genre);
        }

        return enum_value(TagType::tryFrom($this->filter));
    }
}
