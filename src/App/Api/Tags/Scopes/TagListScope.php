<?php

declare(strict_types=1);

namespace App\Api\Tags\Scopes;

use Domain\Tags\QueryBuilders\TagQueryBuilder;

readonly class TagListScope
{
    public function __construct(
        public readonly ?string $type = null,
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
