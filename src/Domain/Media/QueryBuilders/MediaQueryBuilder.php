<?php

declare(strict_types=1);

namespace Domain\Media\QueryBuilders;

use Domain\Media\Models\Media;
use Illuminate\Database\Eloquent\Builder;

class MediaQueryBuilder extends Builder
{
    public function findByUuidOrFail(string $uuid): Media
    {
        return $this->where('uuid', $uuid)->firstOrFail();
    }
}
