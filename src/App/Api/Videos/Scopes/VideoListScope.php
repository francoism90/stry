<?php

declare(strict_types=1);

namespace App\Api\Videos\Scopes;

use Domain\Tags\Models\Tag;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

readonly class VideoListScope
{
    public function __construct(
        public readonly Tag|array|string|null $tags = null,
        public readonly ?string $list = null,
        public readonly ?string $sort = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->verified()
            ->when($this->tags, fn ($query, $tags) => $query->withAllTagsOfAnyType($tags))
            ->when($this->list === null, fn ($query) => $query->inRandomOrder())
            ->when($this->list === 'newest', fn ($query) => $query->latest());
    }
}
