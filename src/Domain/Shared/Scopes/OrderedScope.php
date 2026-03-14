<?php

declare(strict_types=1);

namespace Domain\Shared\Scopes;

use Laravel\Scout\Builder;

readonly class OrderedScope
{
    public function __construct(
        public ?string $order = null,
        public ?string $direction = null,
        public ?bool $disabled = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $order = $this->normalizeOrder($this->order ?? '');

        $scout->when(
            ! $this->disabled && filled($order),
            fn (Builder $scout) => $scout->orderBy(
                $order,
                $this->direction === 'desc' ? 'desc' : 'asc',
            )
        );
    }

    protected function normalizeOrder(string $order): string
    {
        return match ($order) {
            'newest' => 'created_at',
            'oldest' => 'created_at',
            'released' => 'released_at',
            'published' => 'published_at',
            default => $order,
        };
    }
}
