<?php

declare(strict_types=1);

namespace App\Web\Tags\Scopes;

use App\Api\Tags\Requests\TagIndexRequest;
use Domain\Tags\QueryBuilders\TagQueryBuilder;

class TagIndexScope
{
    public function __construct(
        protected readonly TagIndexRequest $request,
    ) {}

    public function __invoke(TagQueryBuilder $query): void
    {
        $query
            ->when($this->request->input('type'), fn ($query, $type) => $query->where('type', $type))
            ->ordered();
    }
}
