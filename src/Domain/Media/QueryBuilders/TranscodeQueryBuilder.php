<?php

declare(strict_types=1);

namespace Domain\Media\QueryBuilders;

use Domain\Media\States\Completed;
use Domain\Media\States\Failed;
use Domain\Media\States\Pending;
use Domain\Media\States\Processing;
use Illuminate\Database\Eloquent\Builder;

class TranscodeQueryBuilder extends Builder
{
    public function pending(): self
    {
        return $this->whereState('state', Pending::class);
    }

    public function processing(): self
    {
        return $this->whereState('state', Processing::class);
    }

    public function completed(): self
    {
        return $this->whereState('state', Completed::class);
    }

    public function failed(): self
    {
        return $this->whereState('state', Failed::class);
    }

    public function successful(): self
    {
        return $this->completed();
    }

    public function inProgress(): self
    {
        return $this->where(function ($query) {
            $query->pending()->orWhere->processing();
        });
    }
}
