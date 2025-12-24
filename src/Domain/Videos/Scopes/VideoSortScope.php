<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoSort;
use Laravel\Scout\Builder;

readonly class VideoSortScope
{
    public function __construct(
        public VideoSort|string|null $sort = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isSort(VideoSort::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isSort(VideoSort::Ordered), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isSort(VideoSort::Longest), fn (Builder $scout) => $scout->orderByDesc('duration'))
            ->when($this->isSort(VideoSort::Shortest), fn (Builder $scout) => $scout->orderBy('duration'));
    }

    protected function isSort(VideoSort ...$values): bool
    {
        $currentSorter = $this->getSorter();

        return $currentSorter && in_array($currentSorter, $values, true);
    }

    protected function getSorter(): ?VideoSort
    {
        $sortValue = $this->sort ?? '';

        return $sortValue instanceof VideoSort
            ? $sortValue
            : VideoSort::tryFrom($sortValue);
    }
}
