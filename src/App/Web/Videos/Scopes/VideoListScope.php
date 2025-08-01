<?php

declare(strict_types=1);

namespace App\Web\Videos\Scopes;

use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoListScope
{
    public function __construct(
        protected readonly int $perPage = 3,
        protected readonly int $page = 1,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void {}
}
