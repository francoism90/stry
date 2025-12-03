<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Enums\VideoSort;
use Laravel\Scout\Builder;

readonly class VideoSortScope
{
    public function __construct(
        public VideoOrder|string|null $sort = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isSort(VideoOrder::Recommended) && blank($scout->query), fn (Builder $scout) => $scout->randomOrder())
            ->when($this->isSort(VideoOrder::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isSort(VideoOrder::Ordered), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isSort(VideoOrder::Longest), fn (Builder $scout) => $scout->orderByDesc('duration'))
            ->when($this->isSort(VideoOrder::Shortest), fn (Builder $scout) => $scout->orderBy('duration'));
    }

    protected function isSort(VideoOrder ...$values): bool
    {
        $currentSorter = $this->getSorter();

        return $currentSorter && in_array($currentSorter, $values, true);
    }

    protected function getSorter(): ?VideoOrder
    {
        $currentSorter = $this->sort;

        return $currentSorter instanceof VideoOrder
            ? $currentSorter
            : VideoOrder::tryFrom($currentSorter);
    }
}
