<?php

declare(strict_types=1);

namespace Domain\Tags\Filters;

use Domain\Tags\Enums\TagScope;
use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class TagScopeFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        $scope = TagScope::tryFrom($value);

        if (! $scope instanceof TagScope || $scope === TagScope::All) {
            return;
        }

        $query->where('type', $scope->value);
    }
}
