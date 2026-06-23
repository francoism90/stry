<?php

declare(strict_types=1);

namespace Support\Scout\Filters;

use Domain\Tags\Models\Tag;
use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class FilterTagged implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (blank($value) || ! is_string($value)) {
            return;
        }

        $tag = Tag::findFromUlid($value);

        if (! $tag) {
            return;
        }

        $query->whereIn('tagged', [$tag->getKey()]);
    }
}
