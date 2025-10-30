<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Laravel\Scout\Builder;

class VideoOrderScope
{
    public function __construct(
        protected VideoOrder|string|null $order = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->when($this->hasOrder(VideoOrder::Newest), fn ($query) => $query->latest())
            ->when($this->hasOrder(VideoOrder::Ordered), fn ($query) => $query->orderBy('name'))
            ->when($this->hasOrder(VideoOrder::Longest), fn ($query) => $query->orderByDesc('duration'))
            ->when($this->hasOrder(VideoOrder::Shortest), fn ($query) => $query->orderBy('duration'))
            ->when($this->hasOrder(VideoOrder::Recommended), fn ($query) => $query->orderBy('_rand()'));
    }

    protected function hasOrder(VideoOrder $value): bool
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
