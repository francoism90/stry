<?php

declare(strict_types=1);

namespace Domain\Media\QueryBuilders;

use Domain\Media\Models\Media;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Media
 *
 * @extends Builder<TModel>
 */
class MediaQueryBuilder extends Builder
{
    public function findByUuidOrFail(string $uuid): Media
    {
        return $this->where('uuid', $uuid)->firstOrFail();
    }

    public function totalSize(): int
    {
        return (int) $this->sum('size');
    }

    /**
     * Media whose longest stream lasts up to the given number of seconds. Media without stream durations are left out.
     *
     * @return self<TModel>
     */
    public function whereDurationAtMost(float $seconds): self
    {
        return $this->whereRaw("(select max((stream->>'duration')::numeric) from jsonb_array_elements((custom_properties->'streams')::jsonb) as stream) <= ?", [$seconds]);
    }
}
