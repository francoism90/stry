<?php

declare(strict_types=1);

namespace App\Web\Videos\Scopes;

use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoListScope
{
    public function __construct(
        protected readonly ?string $type = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->with(['tags'])
            ->orderByDesc('created_at');
    }
}
