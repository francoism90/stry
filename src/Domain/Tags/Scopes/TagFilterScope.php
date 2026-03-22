<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagOrder;
use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Laravel\Scout\Builder;

use function Illuminate\Support\enum_value;

readonly class TagFilterScope
{
    public function __construct(
        public TagType|string|null $type = null,
        public TagOrder|string|null $order = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $defaultOrder = $this->isOrderDefault() && (blank($scout->query) || $scout->query === '*');

        $scout
            ->query(fn (TagQueryBuilder $scout) => $scout->withCount('videos'))
            ->when($this->getType(), fn (Builder $scout, TagType $type) => $scout->where('type', enum_value($type)))
            ->when($defaultOrder, fn (Builder $scout) => $scout->orderByDesc('videos'))
            ->when($this->isOrder(TagOrder::Name), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isOrder(TagOrder::Videos), fn (Builder $scout) => $scout->orderByDesc('videos'))
            ->when($this->isOrder(TagOrder::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isOrder(TagOrder::Oldest), fn (Builder $scout) => $scout->orderBy('created_at'));
    }

    protected function getType(): ?TagType
    {
        $typeValue = $this->type ?? null;

        return is_string($typeValue) ? TagType::tryFrom($typeValue) : $typeValue;
    }

    protected function getOrder(): TagOrder
    {
        $orderValue = $this->order ?? TagOrder::Default;

        return is_string($orderValue) ? TagOrder::from($orderValue) : $orderValue;
    }

    protected function isOrderDefault(): bool
    {
        return $this->getOrder() === TagOrder::Default;
    }

    protected function isOrder(TagOrder ...$values): bool
    {
        $current = $this->getOrder();

        return in_array($current, $values, true);
    }
}
