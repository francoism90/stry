<?php

declare(strict_types=1);

namespace Support\Scout\Filters;

use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class FiltersAdult implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (blank($value)) {
            return;
        }

        $query->where('adult', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }
}
