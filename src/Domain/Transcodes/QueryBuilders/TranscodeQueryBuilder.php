<?php

declare(strict_types=1);

namespace Domain\Transcodes\QueryBuilders;

use ArrayAccess;
use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\States\Completed;
use Domain\Transcodes\States\Failed;
use Domain\Transcodes\States\Pending;
use Domain\Transcodes\States\Processing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class TranscodeQueryBuilder extends Builder
{
    public function encoder(ArrayAccess|array|TranscodeEncoder $encoder): self
    {
        return $this->whereIn('encoder', Arr::wrap($encoder));
    }

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

    public function ordered(): self
    {
        return $this
            ->orderByDesc('transcoded_at')
            ->latest();
    }
}
