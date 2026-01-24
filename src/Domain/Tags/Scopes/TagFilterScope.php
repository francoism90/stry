<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Laravel\Scout\Builder;

use function Illuminate\Support\enum_value;

readonly class TagFilterScope
{
    public function __construct(
        public TagType|string|null $type = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        // Determine if we should use placeholder results
        $defaultOrder = blank($scout->query) || $scout->query === '*';

        $scout
            ->query(fn (TagQueryBuilder $scout) => $scout->withCount('videos'))
            ->when($defaultOrder, fn (Builder $scout) => $scout->orderByDesc('videos'))
            ->when($this->getType(), fn (Builder $scout, TagType $type) => $scout->where('type', enum_value($type))->orderBy('name'));
    }

    protected function getType(): ?TagType
    {
        $typeValue = $this->type ?? '';

        return is_string($typeValue) ? TagType::tryFrom($typeValue) : $typeValue;
    }
}
