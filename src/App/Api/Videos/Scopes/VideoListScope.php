<?php

declare(strict_types=1);

namespace App\Api\Videos\Scopes;

use Domain\Videos\QueryBuilders\VideoQueryBuilder;

readonly class VideoListScope
{
    public function __construct(
        public readonly ?string $list = null,
        public readonly ?string $sort = null,
        public readonly ?array $tags = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->verified()
            ->when($this->tags, fn (VideoQueryBuilder $query, array $tags) => $query->withAllTagsOfAnyType($tags))
            ->when($this->list === null, fn (VideoQueryBuilder $query) => $query->inRandomOrder())
            ->when($this->list === 'watching', fn (VideoQueryBuilder $query) => $query->watching())
            ->when($this->list === 'newest', fn (VideoQueryBuilder $query) => $query->latest());
    }
}
