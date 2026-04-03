<?php

declare(strict_types=1);

namespace Domain\Profiles\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class ProfileQueryBuilder extends Builder
{
    public function ordered(): self
    {
        return $this
            ->orderByDesc('is_primary')
            ->orderBy('name');
    }
}
