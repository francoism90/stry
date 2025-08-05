<?php

declare(strict_types=1);

namespace App\Api\Videos\Scopes;

use Laravel\Scout\Builder;

class VideoFilterScope
{
    public function __construct(
        public readonly ?array $tags = null,
        public readonly ?string $sort = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->when($this->tags, fn ($query, $tags) => $query->whereIn('tagged', $tags))
            ->when($this->sort === 'ordered', fn ($query) => $query->orderBy('name'))
            ->when($this->sort === 'longest', fn ($query) => $query->orderByDesc('duration'))
            ->when($this->sort === 'shortest', fn ($query) => $query->orderBy('duration'));
    }
}
