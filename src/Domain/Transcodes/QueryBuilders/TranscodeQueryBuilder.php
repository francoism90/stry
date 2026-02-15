<?php

declare(strict_types=1);

namespace Domain\Transcodes\QueryBuilders;

use Domain\Transcodes\States\Completed;
use Domain\Transcodes\States\Failed;
use Domain\Transcodes\States\Pending;
use Domain\Transcodes\States\Processing;
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

    public function active(): self
    {
        return $this
            ->whereNot(fn ($query) => $query->failed())
            ->ordered();
    }
}
