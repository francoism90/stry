<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use ArrayAccess;
use Domain\Tags\Models\Tag;
use Laravel\Scout\Builder;

readonly class VideoTagScope
{
    public function __construct(
        protected ArrayAccess|array|Tag|string|null $tags = null,
    ) {}

    public function __invoke(Builder $scout): void
    {
        $scout
            ->when($this->getIds(), fn (Builder $scout, array $tags) => $scout->where('tagged', $tags));
    }

    protected function getIds(): ?array
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
