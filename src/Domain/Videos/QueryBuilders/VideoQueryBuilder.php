<?php

declare(strict_types=1);

namespace Domain\Videos\QueryBuilders;

use Domain\Videos\States\Verified;
use Illuminate\Database\Eloquent\Builder;

class VideoQueryBuilder extends Builder
{
    public function verified(): self
    {
        return $this->whereState('state', Verified::class);
    }

    public function recent(): self
    {
        return $this
            ->orderByDesc('created_at')
            ->orderByDesc('released_at');
    }
}
