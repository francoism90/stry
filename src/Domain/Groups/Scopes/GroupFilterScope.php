<?php

declare(strict_types=1);

namespace Domain\Groups\Scopes;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
use Laravel\Scout\Builder;

use function Illuminate\Support\enum_value;

readonly class GroupFilterScope
{
    public function __construct(
        public GroupType|string|null $type = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $defaultOrder = blank($scout->query) || $scout->query === '*';

        $scout
            ->query(fn (GroupQueryBuilder $query) => $query->withCount('videos'))
            ->when($defaultOrder, fn (Builder $scout) => $scout->orderByDesc('updated_at'))
            ->when($this->getType(), fn (Builder $scout, GroupType $type) => $scout->where('type', enum_value($type)));
    }

    protected function getType(): ?GroupType
    {
        $typeValue = $this->type ?? null;

        return is_string($typeValue) ? GroupType::tryFrom($typeValue) : $typeValue;
    }
}
