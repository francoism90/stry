<?php

declare(strict_types=1);

namespace App\Api\Tags\Scopes;

use Domain\Tags\QueryBuilders\TagQueryBuilder;

class TagListScope
{
    public function __construct(
        public readonly ?string $type = null,
    ) {}

    public function __invoke(TagQueryBuilder $query): void
    {
        $query
            ->withCount('videos')
            ->when($this->type, fn ($query, $type) => $query->where('type', $type))
            ->ordered();
    }
}
