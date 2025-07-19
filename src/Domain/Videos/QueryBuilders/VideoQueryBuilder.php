<?php

declare(strict_types=1);

namespace Domain\Videos\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class VideoQueryBuilder extends Builder
{
    public function published(): self
    {
        return $this->whereDate('published_at', '<=', now());
    }

    public function recent(): self
    {
        return $this
            ->orderByDesc('created_at')
            ->orderByDesc('released_at');
    }
}
