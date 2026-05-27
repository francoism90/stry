<?php

declare(strict_types=1);

namespace Support\Scout\Filters;

use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class FiltersUntagged implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $previousCallback = $query->callback;

        $query->callback = function ($typesense, $scoutQuery, $options) use ($previousCallback) {
            $options['filter_by'] = filled($options['filter_by'] ?? '')
                ? sprintf('%s && tagged:=[]', $options['filter_by'])
                : 'tagged:=[]';

            if ($previousCallback) {
                return $previousCallback($typesense, $scoutQuery, $options);
            }

            return $typesense->search($options);
        };
    }
}
