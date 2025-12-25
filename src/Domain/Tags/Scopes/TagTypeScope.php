<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;
use Laravel\Scout\Builder;

use function Illuminate\Support\enum_value;

readonly class TagTypeScope
{
    public function __construct(
        public TagType|string|null $type = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->query(fn (TagQueryBuilder $scout) => $scout->withCount('videos'))
            ->when(blank($scout->query), fn (Builder $scout) => $scout->orderByDesc('videos'))
            ->when($this->getType(), fn (Builder $scout, TagType $type) => $scout->where('type', enum_value($type))->orderBy('name'));
    }

    protected function getType(): ?TagType
    {
        $typeValue = $this->type ?? '';

        return is_string($typeValue) ? TagType::from($typeValue) : $typeValue;
    }
}
