<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Illuminate\Contracts\Support\Arrayable;
use Laravel\Scout\Builder;

class VideoSearchScope
{
    public function __construct(
        protected ?string $sort = null,
        protected Arrayable|array|null $tags = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->query(fn (VideoQueryBuilder $query) => $query->verified()->with('tags'))
            ->when(blank($query->query), fn (Builder $query) => $query->where('id', 0))
            ->when($this->tags, fn (Builder $query, array $tags) => $query->whereIn('tagged', $tags))
            ->when($this->sort === 'newest', fn (Builder $query) => $query->latest())
            ->when($this->sort === 'oldest', fn (Builder $query) => $query->oldest())
            ->when($this->sort === 'ordered', fn (Builder $query) => $query->orderBy('name'))
            ->when($this->sort === 'longest', fn (Builder $query) => $query->orderByDesc('duration'))
            ->when($this->sort === 'shortest', fn (Builder $query) => $query->orderBy('duration'));
    }
}
