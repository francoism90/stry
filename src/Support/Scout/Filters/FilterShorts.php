<?php

declare(strict_types=1);

namespace Support\Scout\Filters;

use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class FilterShorts implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $query->where('duration', '<=', 300);
    }
}
