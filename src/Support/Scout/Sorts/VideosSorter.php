<?php

declare(strict_types=1);

namespace Support\Scout\Sorts;

use Foxws\ScoutBuilder\Sorts\Sort;
use Laravel\Scout\Builder;

class VideosSorter implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $query->orderByDesc('videos');
    }
}
