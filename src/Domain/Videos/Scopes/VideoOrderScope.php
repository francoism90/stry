<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoOrder;
use Laravel\Scout\Builder;

readonly class VideoOrderScope
{
    public function __construct(
        public VideoOrder|string|null $order = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->isOrder(VideoOrder::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isOrder(VideoOrder::Ordered), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isOrder(VideoOrder::Longest), fn (Builder $scout) => $scout->orderByDesc('duration'))
            ->when($this->isOrder(VideoOrder::Shortest), fn (Builder $scout) => $scout->orderBy('duration'));
    }

    protected function isOrder(VideoOrder ...$values): bool
    {
        $currentOrderer = $this->getOrderer();

        return $currentOrderer && in_array($currentOrderer, $values, true);
    }

    protected function getOrderer(): ?VideoOrder
    {
        $orderValue = $this->order ?? '';

        return $orderValue instanceof VideoOrder
            ? $orderValue
            : VideoOrder::tryFrom($orderValue);
    }
}
