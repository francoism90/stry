<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\QueryBuilders\TagQueryBuilder;

class TagFilterScope
{
    public function __construct(
        protected readonly ?string $type = null,
    ) {}

    public function __invoke(TagQueryBuilder $query): void
    {
        $query
            ->withCount('videos')
            ->when($this->type,
                fn (TagQueryBuilder $query, string $type) => $query->type($type)->ordered(),
                fn (TagQueryBuilder $query) => $query->inRandomOrder()
            );
    }
}
