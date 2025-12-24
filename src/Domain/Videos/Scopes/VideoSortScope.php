<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoSort;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Request;
use Laravel\Scout\Builder;

readonly class VideoSortScope
{
    public function __construct(
        public VideoSort|string|null $sort = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isSort(VideoSort::Relevant) && blank($scout->query), fn (Builder $scout) => $scout->randomOrder($this->getRandomSeed()))
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
        $sortValue = $this->sort;

        return $sortValue instanceof VideoSort
            ? $sortValue
            : VideoSort::tryFrom($sortValue);
    }

    protected function getRandomSeed(): int
    {
        /** @var Repository $sessionCache */
        $sessionCache = Request::session()->cache();

        if (! $sessionCache->has('video-random-seed')) {
            $sessionCache->put('video-random-seed', random_int(1, 1000), now()->addMinutes(30));
        }

        return $sessionCache->get('video-random-seed', 1000);
    }
}
