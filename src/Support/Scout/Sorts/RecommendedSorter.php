<?php

declare(strict_types=1);

namespace Support\Scout\Sorts;

use Foxws\ScoutBuilder\Sorts\Sort;
use Laravel\Scout\Builder;

class RecommendedSorter implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        // If the query is already sorted by something else, or if there are any orders applied, we don't want to apply the recommended sorting.
        if (($query->options['sort_by'] ?? null) || ! empty($query->orders)) {
            return;
        }

        // Get the search query from the builder.
        $search = $query->query ?? '';

        // If the search query is blank or just a wildcard, apply random sorting to surface a variety of results.
        if (blank($search) || $search === '*') {
            $query->randomOrder();
        }
    }
}
