<?php

declare(strict_types=1);

namespace Domain\Groups\Scopes;

use Domain\Groups\Enums\GroupOrder;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
use Laravel\Scout\Builder;

use function Illuminate\Support\enum_value;

readonly class GroupFilterScope
{
    public function __construct(
        public GroupType|string|null $type = null,
        public GroupOrder|string|null $order = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $defaultOrder = $this->isOrderDefault() && (blank($scout->query) || $scout->query === '*');

        $scout
            ->query(fn (GroupQueryBuilder $query) => $query->withCount('groupables'))
            ->when($this->getType(), fn (Builder $scout, GroupType $type) => $scout->where('type', enum_value($type)))
            ->when($defaultOrder, fn (Builder $scout) => $scout->orderByDesc('updated_at'))
            ->when($this->isOrder(GroupOrder::Name), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isOrder(GroupOrder::Videos), fn (Builder $scout) => $scout->orderByDesc('groupables'))
            ->when($this->isOrder(GroupOrder::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isOrder(GroupOrder::Oldest), fn (Builder $scout) => $scout->orderBy('created_at'))
            ->when($this->isOrder(GroupOrder::Updated), fn (Builder $scout) => $scout->orderByDesc('updated_at'));
    }

    protected function getType(): ?GroupType
    {
        $typeValue = $this->type ?? null;

        return is_string($typeValue) ? GroupType::tryFrom($typeValue) : $typeValue;
    }

    protected function getOrder(): GroupOrder
    {
        $orderValue = $this->order ?? GroupOrder::Default;

        return is_string($orderValue) ? GroupOrder::from($orderValue) : $orderValue;
    }

    protected function isOrderDefault(): bool
    {
        return $this->getOrder() === GroupOrder::Default;
    }

    protected function isOrder(GroupOrder ...$values): bool
    {
        $current = $this->getOrder();

        return in_array($current, $values, true);
    }
}
