<?php

declare(strict_types=1);

namespace Domain\Groups\Filters;

use Domain\Groups\Enums\GroupScope;
use Domain\Groups\Enums\GroupType;
use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class GroupScopeFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        match (GroupScope::tryFrom($value)) {
            GroupScope::Custom => $query->where('type', GroupType::Custom->value),
            default => null,
        };
    }
}
