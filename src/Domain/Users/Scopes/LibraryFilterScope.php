<?php

declare(strict_types=1);

namespace Domain\Users\Scopes;

use ArrayAccess;
use Domain\Tags\Models\Tag;
use Domain\Users\Enums\LibraryFilter;
use Domain\Videos\Enums\VideoList;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class LibraryFilterScope
{
    public function __construct(
        protected LibraryFilter|string|null $filter = null,
    ) {}

    public function __invoke(VideoQueryBuilder $builder): void
    {
        $builder
            ->when($this->isFilter(LibraryFilter::Watching), fn (VideoQueryBuilder $builder) => $builder->watching());
    }

    protected function getFilter(): ?LibraryFilter
    {
        if (! $this->filter) {
            return null;
        }

        return $this->filter instanceof LibraryFilter
            ? $this->filter
            : LibraryFilter::tryFrom($this->filter);
    }

    protected function isFilter(...$values): bool
    {
        $filterValue = $this->getFilter();

        return $filterValue && in_array($filterValue, $values, true);
    }

    protected function hasFilter(): bool
    {
        return filled($this->getFilter());
    }
}
