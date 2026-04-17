<?php

declare(strict_types=1);

namespace Support\Scout\Sorts;

use Foxws\ScoutBuilder\Sorts\Sort;
use Laravel\Scout\Builder;

class RecommendedSorter implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        // If the query already has a sort order defined, we won't apply our custom sorting logic to avoid conflicts.
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
