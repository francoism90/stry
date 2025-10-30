<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoOrder;
use Laravel\Scout\Builder;

class VideoOrderScope
{
    public function __construct(
        protected VideoOrder|string|null $order = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->when($this->isOrder(VideoOrder::Newest), fn ($query) => $query->latest())
            ->when($this->isOrder(VideoOrder::Ordered), fn ($query) => $query->orderBy('name'))
            ->when($this->isOrder(VideoOrder::Longest), fn ($query) => $query->orderByDesc('duration'))
            ->when($this->isOrder(VideoOrder::Shortest), fn ($query) => $query->orderBy('duration'))
            ->when($this->isOrder(VideoOrder::Recommended), fn ($query) => $query->orderBy('_rand()'))
            ->unless($this->getOrder(), fn ($query) => $query->orderBy('_rand()'));
    }

    protected function isOrder(VideoOrder $value): bool
    {
        return $this->getOrder() === $value;
    }

    protected function getOrder(): ?VideoOrder
    {
        if (! $this->order) {
            return null;
        }

        return VideoOrder::tryFrom($this->order);
    }
}
