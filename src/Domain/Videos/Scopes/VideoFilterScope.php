<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoFilter;
use Laravel\Scout\Builder;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Request;

readonly class VideoFilterScope
{
    public function __construct(
        public VideoFilter|string|null $filter = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isFilter(VideoFilter::History), fn (Builder $scout) => $scout->query(fn ($query) => $query->watching()))
            ->when($this->isFilter(VideoFilter::Watchlist), fn (Builder $scout) => $scout->where('duration', ['<=', 600]));
    }

    protected function isFilter(?VideoFilter ...$values): bool
    {
        $currentFilter = $this->getFilter();

        return $currentFilter && in_array($currentFilter, $values, true);
    }

    protected function getFilter(): ?VideoFilter
    {
        $currentFilter = $this->filter;

        if (blank($currentFilter)) {
            return null;
        }

        return $currentFilter instanceof VideoFilter
            ? $currentFilter
            : VideoFilter::tryFrom($currentFilter);
    }

    protected function getRandomSeed(): int
    {
        /** @var Repository $sessionCache */
        $sessionCache = Request::session()->cache();

        if (! $sessionCache->has('video:random-seed')) {
            $sessionCache->put('video:random-seed', random_int(1, 1000), now()->addHour());
        }

        return $sessionCache->get('video:filter-seed', 1000);
    }
}
