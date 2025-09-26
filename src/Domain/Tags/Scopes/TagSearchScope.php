<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Laravel\Scout\Builder;

class TagSearchScope
{
    public function __construct(
        protected readonly ?string $type = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->when(blank($query->query), fn (Builder $query) => $query->orderByDesc('videos'));
    }
}
