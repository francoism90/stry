<?php

declare(strict_types=1);

namespace Domain\Transcodes\QueryBuilders;

use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\Models\Transcode;
use Domain\Transcodes\States;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * @template TModel of Transcode
 *
 * @extends Builder<TModel>
 */
class TranscodeQueryBuilder extends Builder
{
    /**
     * @param  array<array-key, TranscodeEncoder|string>|TranscodeEncoder  $encoder
     * @return self<TModel>
     */
    public function encoder(array|TranscodeEncoder $encoder): self
    {
        return $this->whereIn('encoder', Arr::wrap($encoder));
    }

    /** @return self<TModel> */
    public function pending(): self
    {
        return $this->whereState('state', States\Pending::class);
    }

    /** @return self<TModel> */
    public function processing(): self
    {
        return $this->whereState('state', States\Processing::class);
    }

    /** @return self<TModel> */
    public function completed(): self
    {
        return $this->whereState('state', States\Completed::class);
    }

    /** @return self<TModel> */
    public function failed(): self
    {
        return $this->whereState('state', States\Failed::class);
    }

    /** @return self<TModel> */
    public function successful(): self
    {
        return $this->completed();
    }

    /** @return self<TModel> */
    public function expired(): self
    {
        return $this
            ->whereState('state', [States\Failed::class, States\Imported::class])
            ->where('created_at', '<=', now()->subDays(7));
    }

    /** @return self<TModel> */
    public function prunable(): self
    {
        return $this
            ->expired()
            ->orWhere(fn ($query) => $query->failed())
            ->oldest();
    }

    /** @return self<TModel> */
    public function current(): self
    {
        return $this
            ->whereNot(fn ($query) => $query->failed())
            ->ordered();
    }

    /** @return self<TModel> */
    public function ordered(): self
    {
        return $this
            ->orderByDesc('transcoded_at')
            ->latest();
    }
}
