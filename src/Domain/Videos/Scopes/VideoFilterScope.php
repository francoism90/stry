<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use ArrayAccess;
use Domain\Tags\Models\Tag;
use Laravel\Scout\Builder;

class VideoFilterScope
{
    public function __construct(
        protected ?string $filter = null,
        protected ArrayAccess|array|Tag|string|null $tags = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->getTags(), fn (Builder $scout, array $tags) => $scout->where('tagged', $tags))
            ->when($this->isFilter('newest'), fn (Builder $scout) => $scout->latest())
            ->when($this->isFilter('ordered'), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isFilter('longest'), fn (Builder $scout) => $scout->orderByDesc('duration'))
            ->when($this->isFilter('shortest'), fn (Builder $scout) => $scout->orderBy('duration'))
            ->when($this->isFilter('default', 'recommended') && blank($scout->query), fn ($scout) => $scout->orderBy('_rand()'));
    }

    protected function getFilter(): ?string
    {
        return $this->filter;
    }

    protected function hasFilter(): bool
    {
        return filled($this->getFilter());
    }

    protected function isFilter(...$values): bool
    {
        $filterValue = $this->getFilter();

        return $this->hasFilter() && in_array($filterValue, $values, true);
    }

    protected function getTags(): ?array
    {
        if (! $this->hasTags()) {
            return null;
        }

        return Tag::query()
            ->hasUlid($this->tags)
            ->pluck('id')
            ->toArray();
    }

    protected function hasTags(): bool
    {
        return filled($this->tags);
    }
}
