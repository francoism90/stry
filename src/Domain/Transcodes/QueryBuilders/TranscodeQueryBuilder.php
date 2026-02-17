<?php

declare(strict_types=1);

namespace Domain\Transcodes\QueryBuilders;

use ArrayAccess;
use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\States;
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
        return $this->whereState('state', States\Pending::class);
    }

    public function processing(): self
    {
        return $this->whereState('state', States\Processing::class);
    }

    public function completed(): self
    {
        return $this->whereState('state', States\Completed::class);
    }

    public function failed(): self
    {
        return $this->whereState('state', States\Failed::class);
    }

    public function successful(): self
    {
        return $this->completed();
    }

    public function expired(): self
    {
        return $this
            ->whereState('state', [States\Failed::class, States\Imported::class])
            ->where('created_at', '<=', now()->subDays(7));
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
