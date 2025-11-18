<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use ArrayAccess;
use Domain\Tags\Models\Tag;
use Domain\Videos\Enums\VideoFilter;
use Laravel\Scout\Builder;

readonly class VideoFilterScope
{
    public function __construct(
        protected VideoFilter|string|null $filter = null,
        protected ArrayAccess|array|Tag|string|null $tags = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->getTags(), fn (Builder $scout, array $tags) => $scout->where('tagged', $tags))
            ->when($this->isFilter(VideoFilter::Recommended) && blank($scout->query), fn (Builder $scout) => $scout->randomOrder())
            ->when($this->isFilter(VideoFilter::Newest), fn (Builder $scout) => $scout->latest())
            ->when($this->isFilter(VideoFilter::Ordered), fn (Builder $scout) => $scout->orderBy('name'))
            ->when($this->isFilter(VideoFilter::Longest), fn (Builder $scout) => $scout->orderByDesc('duration'))
            ->when($this->isFilter(VideoFilter::Shortest), fn (Builder $scout) => $scout->orderBy('duration'));
    }

    protected function getFilter(): ?VideoFilter
    {
        if (! $this->filter) {
            return null;
        }

        return $this->filter instanceof VideoFilter
            ? $this->filter
            : VideoFilter::tryFrom($this->filter);
    }

    protected function isFilter(VideoFilter ...$values): bool
    {
        $filterValue = $this->getFilter();

        return $filterValue && in_array($filterValue, $values, true);
    }

    protected function hasFilter(): bool
    {
        return filled($this->getFilter());
    }

    protected function getTags(): ?array
    {
        if (! filled($this->tags)) {
            return null;
        }

        return Tag::query()
            ->options($this->tags)
            ->pluck('id')
            ->toArray();
    }
}
