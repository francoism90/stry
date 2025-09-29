<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\Enums\VideoType;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Illuminate\Contracts\Support\Arrayable;
use Laravel\Scout\Builder;

class VideoSearchScope
{
    public function __construct(
        protected Arrayable|array|null $tags = null,
        protected VideoType|string|null $type = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->query(fn (VideoQueryBuilder $query) => $query->verified()->with('tags'))
            ->when($this->tags, fn (Builder $query, array $tags) => $query->whereIn('tagged', $tags))
            ->when($this->type === 'newest', fn (Builder $query) => $query->latest())
            ->when($this->type === 'oldest', fn (Builder $query) => $query->oldest())
            ->when($this->type === 'ordered', fn (Builder $query) => $query->orderBy('name'))
            ->when($this->type === 'longest', fn (Builder $query) => $query->orderByDesc('duration'))
            ->when($this->type === 'shortest', fn (Builder $query) => $query->orderBy('duration'));
    }
}
