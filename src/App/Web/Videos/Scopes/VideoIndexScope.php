<?php

declare(strict_types=1);

namespace App\Web\Videos\Scopes;

use App\Web\Videos\Requests\VideoIndexRequest;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoIndexScope
{
    public function __construct(
        protected readonly VideoIndexRequest $request,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->with(['tags'])
            ->orderByDesc('created_at');
    }
}
