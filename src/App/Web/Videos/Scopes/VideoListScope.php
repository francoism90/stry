<?php

declare(strict_types=1);

namespace App\Web\Videos\Scopes;

use Domain\Tags\Models\Tag;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoListScope
{
    public function __construct(
        public readonly Tag|array|null $tags = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->with(['tags'])
            ->when($this->tags, fn ($query, $tags) => $query->withAllTags($tags));
    }
}
