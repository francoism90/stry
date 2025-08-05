<?php

declare(strict_types=1);

namespace App\Api\Videos\Scopes;

use Domain\Tags\Models\Tag;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoListScope
{
    public function __construct(
        public readonly Tag|array|null $tags = null,
        public readonly ?string $list = null,
        public readonly ?string $sort = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->when($this->tags, fn ($query, $tags) => $query->withAllTags($tags))
            ->when($this->list === null, fn ($query) => $query->inRandomOrder());
    }
}
