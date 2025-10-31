<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoOrder;
use Laravel\Scout\Builder;

class VideoFilterScope
{
    public function __construct(
        protected VideoOrder|string|null $filter = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isFilter(VideoOrder::Newest), fn ($scout) => $scout->latest())
            ->when($this->isFilter(VideoOrder::Ordered), fn ($scout) => $scout->orderBy('name'))
            ->when($this->isFilter(VideoOrder::Longest), fn ($scout) => $scout->orderByDesc('duration'))
            ->when($this->isFilter(VideoOrder::Shortest), fn ($scout) => $scout->orderBy('duration'))
            ->unless($this->hasFilter() && filled($scout->query), fn ($scout) => $scout->orderBy('_rand()'));
    }

    protected function hasFilter(): bool
    {
        return $this->getFilter() && $this->getFilter() !== VideoOrder::Recommended;
    }

    protected function isFilter(VideoOrder $value): bool
    {
        return $this->getFilter() === $value;
    }

    protected function getFilter(): ?VideoOrder
    {
        if (! $this->filter) {
            return null;
        }

        return VideoOrder::tryFrom($this->filter);
    }
}
