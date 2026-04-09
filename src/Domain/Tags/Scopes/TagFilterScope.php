<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagSorter;
use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Laravel\Scout\Builder;

use function Illuminate\Support\enum_value;

readonly class TagFilterScope
{
    public function __construct(
        public TagType|string|null $type = null,
        public TagSorter|string|null $sort = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $defaultOrder = $this->isOrderDefault() && (blank($scout->query) || $scout->query === '*');

        $scout
            ->query(fn (TagQueryBuilder $scout) => $scout->withCount('videos'))
            ->when($this->getType(), fn (Builder $scout, TagType $type) => $scout->where('type', enum_value($type)))
            ->when($defaultOrder, fn (Builder $scout) => $scout->orderByDesc('videos'))
            ->when($this->isOrder(TagSorter::Name), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isOrder(TagSorter::Videos), fn (Builder $scout) => $scout->orderByDesc('videos'))
            ->when($this->isOrder(TagSorter::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isOrder(TagSorter::Oldest), fn (Builder $scout) => $scout->orderBy('created_at'));
    }

    protected function getType(): ?TagType
    {
        $typeValue = $this->type ?? null;

        return is_string($typeValue) ? TagType::tryFrom($typeValue) : $typeValue;
    }

    protected function getOrder(): TagSorter
    {
        $sortValue = $this->sort ?? TagSorter::Default;

        return is_string($sortValue) ? TagSorter::from($sortValue) : $sortValue;
    }

    protected function isOrderDefault(): bool
    {
        return $this->getOrder() === TagSorter::Default;
    }

    protected function isOrder(TagSorter ...$values): bool
    {
        $current = $this->getOrder();

        return in_array($current, $values, true);
    }
}
