<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoList;
use Laravel\Scout\Builder;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Request;

readonly class VideoListScope
{
    public function __construct(
        public VideoList|string|null $list = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isList(VideoList::All) && blank($scout->query), fn (Builder $scout) => $scout->randomOrder($this->getRandomSeed()))
            ->when($this->isList(VideoList::History), fn (Builder $scout) => $scout->query(fn ($query) => $query->watching()))
            ->when($this->isList(VideoList::Watchlist), fn (Builder $scout) => $scout->where('duration', ['<=', 600]));
    }

    protected function isList(?VideoList ...$values): bool
    {
        $currentList = $this->getList();

        return $currentList && in_array($currentList, $values, true);
    }

    protected function getList(): ?VideoList
    {
        $currentList = $this->list;

        if (blank($currentList)) {
            return null;
        }

        return $currentList instanceof VideoList
            ? $currentList
            : VideoList::tryFrom($currentList);
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
