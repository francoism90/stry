<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Laravel\Scout\Builder;

class TagSearchScope
{
    public function __construct(
        protected readonly ?string $sort = null,
        protected readonly ?string $type = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->when($this->type === 'popularity', fn (Builder $query) => $query->orderByDesc('videos'));
    }
}
