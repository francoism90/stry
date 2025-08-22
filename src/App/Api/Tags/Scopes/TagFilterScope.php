<?php

declare(strict_types=1);

namespace App\Api\Tags\Scopes;

use Laravel\Scout\Builder;

readonly class TagFilterScope
{
    public function __construct(
        public readonly ?string $sort = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->when($this->sort === 'popularity', fn ($query) => $query->orderByDesc('videos'));
    }
}
