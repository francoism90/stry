<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoFilter;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Request;
use Laravel\Scout\Builder;

readonly class VideoFilterScope
{
    public function __construct(
        public ?VideoFilter $filter = null,
        public ?VideoFilter $default = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isFilterDefault() && blank($scout->query), fn (Builder $scout) => $scout->randomOrder($this->randomSeed()))
            ->when($this->isFilter(VideoFilter::History), fn (Builder $scout) => $scout->query(fn ($query) => $query->watching()));
    }

    protected function getFilter(): VideoFilter
    {
        return $this->filter ?? VideoFilter::Default;
    }

    protected function isFilter(VideoFilter ...$values): bool
    {
        $currentFilter = $this->getFilter();

        return in_array($currentFilter, $values, true);
    }

    protected function isFilterDefault(): bool
    {
        // Determine the default filter
        $defaultFilter = $this->default ?? VideoFilter::Default;

        return $this->getFilter() === $defaultFilter;
    }

    protected function randomSeed(): int
    {
        /** @var Repository $sessionCache */
        $sessionCache = Request::session()->cache();

        return $sessionCache->remember('video:random-seed', now()->addHour(), fn (): int => random_int(1, 1000));
    }
}
