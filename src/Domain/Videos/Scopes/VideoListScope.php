<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoList;
use Laravel\Scout\Builder;

readonly class VideoListScope
{
    public function __construct(
        public VideoList|string|null $type = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isType(VideoList::Recommended) && blank($scout->query), fn (Builder $scout) => $scout->randomOrder())
            ->when($this->isType(VideoList::Watched), fn (Builder $scout) => $scout->latest())
            ->when($this->isType(VideoList::Newest), fn (Builder $scout) => $scout->latest());
    }

    protected function isType(VideoList ...$values): bool
    {
        $currentType = $this->getType();

        return $currentType && in_array($currentType, $values, true);
    }

    protected function getType(): ?VideoList
    {
        $currentType = $this->type;

        return $currentType instanceof VideoList
            ? $currentType
            : VideoList::tryFrom($currentType);
    }
}
