<?php

declare(strict_types=1);

namespace Domain\Groups\Scopes;

use Domain\Groups\Enums\GroupSorter;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
use Laravel\Scout\Builder;

use function Illuminate\Support\enum_value;

readonly class GroupFilterScope
{
    public function __construct(
        public GroupType|string|null $type = null,
        public GroupSorter|string|null $sort = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $defaultOrder = $this->isOrderDefault() && (blank($scout->query) || $scout->query === '*');

        $scout
            ->query(fn (GroupQueryBuilder $query) => $query->withCount('groupables'))
            ->when($this->getType(), fn (Builder $scout, GroupType $type) => $scout->where('type', enum_value($type)))
            ->when($defaultOrder, fn (Builder $scout) => $scout->orderByDesc('updated_at'))
            ->when($this->isOrder(GroupSorter::Name), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isOrder(GroupSorter::Videos), fn (Builder $scout) => $scout->orderByDesc('groupables'))
            ->when($this->isOrder(GroupSorter::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isOrder(GroupSorter::Oldest), fn (Builder $scout) => $scout->orderBy('created_at'))
            ->when($this->isOrder(GroupSorter::Updated), fn (Builder $scout) => $scout->orderByDesc('updated_at'));
    }

    protected function getType(): ?GroupType
    {
        $typeValue = $this->type ?? null;

        return is_string($typeValue) ? GroupType::tryFrom($typeValue) : $typeValue;
    }

    protected function getOrder(): GroupSorter
    {
        $sortValue = $this->sort ?? GroupSorter::Default;

        return is_string($sortValue) ? GroupSorter::from($sortValue) : $sortValue;
    }

    protected function isOrderDefault(): bool
    {
        return $this->getOrder() === GroupSorter::Default;
    }

    protected function isOrder(GroupSorter ...$values): bool
    {
        $current = $this->getOrder();

        return in_array($current, $values, true);
    }
}
