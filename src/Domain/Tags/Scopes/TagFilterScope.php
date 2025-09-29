<?php

declare(strict_types=1);

namespace Domain\Tags\Scopes;

use Domain\Tags\Enums\TagType;
use Domain\Tags\QueryBuilders\TagQueryBuilder;

class TagFilterScope
{
    public function __construct(
        protected TagType|string|null $type = null,
    ) {}

    public function __invoke(TagQueryBuilder $query): void
    {
        $query
            ->withCount('videos')
            ->when($this->type,
                fn (TagQueryBuilder $query, TagType|string $type) => $query->type($type)->ordered(),
                fn (TagQueryBuilder $query) => $query->inRandomOrder()
            );
    }
}
