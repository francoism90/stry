<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoList;
use Laravel\Scout\Builder;

readonly class VideoListScope
{
    public function __construct(
        public VideoList|string|null $list = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isList(VideoList::Recommended) && blank($scout->query), fn (Builder $scout) => $scout->randomOrder())
            ->when($this->isList(VideoList::Watched), fn (Builder $scout) => $scout->latest())
            ->when($this->isList(VideoList::Newest), fn (Builder $scout) => $scout->latest());
    }

    protected function isList(VideoList ...$values): bool
    {
        $currentList = $this->getList();

        return $currentList && in_array($currentList, $values, true);
    }

    protected function getList(): ?VideoList
    {
        $listValue = $this->list;

        return $listValue instanceof VideoList
            ? $listValue
            : VideoList::tryFrom($listValue);
    }
}
