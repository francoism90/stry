<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use ArrayAccess;
use Domain\Tags\Models\Tag;
use Domain\Videos\Enums\VideoList;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoListScope
{
    public function __construct(
        protected VideoList|string|null $filter = null,
        protected Tag|ArrayAccess|string|null $tags = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->verified()
            ->with('tags')
            ->when($this->isFilter(VideoList::Watching), fn ($query) => $query->watching())
            ->unless($this->hasFilter(), fn ($query) => $query->where('id', 0));
    }

    protected function hasFilter(): bool
    {
        return filled($this->getFilter());
    }

    protected function isFilter(VideoList $value): bool
    {
        return $this->getFilter() === $value;
    }

    protected function getFilter(): ?VideoList
    {
        if (! $this->filter) {
            return null;
        }

        return VideoList::tryFrom($this->filter);
    }
}
