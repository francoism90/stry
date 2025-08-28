<?php

declare(strict_types=1);

namespace App\Api\Videos\Scopes;

use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Laravel\Scout\Builder;

readonly class VideoFilterScope
{
    public function __construct(
        public readonly ?string $sort = null,
        public readonly ?array $tags = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->query(fn (VideoQueryBuilder $query) => $query->verified())
            ->when($this->tags, fn (Builder $query, array $tags) => $query->whereIn('tagged', $tags))
            ->when($this->sort === 'ordered', fn (Builder $query) => $query->orderBy('name'))
            ->when($this->sort === 'longest', fn (Builder $query) => $query->orderByDesc('duration'))
            ->when($this->sort === 'shortest', fn (Builder $query) => $query->orderBy('duration'));
    }
}
