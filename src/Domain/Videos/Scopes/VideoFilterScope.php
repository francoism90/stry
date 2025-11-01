<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use ArrayAccess;
use Domain\Tags\Models\Tag;
use Domain\Videos\Enums\VideoOrder;
use Laravel\Scout\Builder;

class VideoFilterScope
{
    public function __construct(
        protected VideoOrder|string|null $filter = null,
        protected Tag|ArrayAccess|string|null $tags = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->hasTags(), fn ($scout) => $scout->where('tagged', $this->getTags()))
            ->when($this->isFilter(VideoOrder::Newest), fn ($scout) => $scout->latest())
            ->when($this->isFilter(VideoOrder::Ordered), fn ($scout) => $scout->orderBy('name'))
            ->when($this->isFilter(VideoOrder::Longest), fn ($scout) => $scout->orderByDesc('duration'))
            ->when($this->isFilter(VideoOrder::Shortest), fn ($scout) => $scout->orderBy('duration'))
            ->when($this->hasFilter() && blank($scout->query), fn ($scout) => $scout->orderBy('_rand()'));
    }

    protected function hasTags(): bool
    {
        return filled($this->tags);
    }

    protected function getTags(): array
    {
        if ($this->tags instanceof Tag) {
            return [$this->tags->getKey()];
        }

        return Tag::query()
            ->whereUlid($this->tags)
            ->pluck('id')
            ->toArray();
    }

    protected function hasFilter(): bool
    {
        return filled($this->getFilter());
    }

    protected function isFilter(VideoOrder $value): bool
    {
        return $this->getFilter() === $value;
    }

    protected function getFilter(): ?VideoOrder
    {
        if (! $this->filter) {
            return null;
        }

        return $this->filter instanceof VideoOrder
            ? $this->filter
            : VideoOrder::tryFrom($this->filter);
    }
}
