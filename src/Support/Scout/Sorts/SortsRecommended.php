<?php

declare(strict_types=1);

namespace Support\Scout\Sorts;

use Foxws\ScoutBuilder\Sorts\Sort;
use Laravel\Scout\Builder;

class SortsRecommended implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        // TODO: Implement a real recommended sort, for now we just randomize the results

        $query->randomOrder();
    }
}
