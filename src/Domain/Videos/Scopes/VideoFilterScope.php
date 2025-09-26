<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Tags\Models\Tag;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoFilterScope
{
    public function __construct(
        protected readonly ?string $type = null,
        protected readonly ?Tag $tag = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->verified()
            ->with('tags')
            ->when($this->type === null, fn (VideoQueryBuilder $query) => $query->inRandomOrder())
            ->when($this->type === 'newest', fn (VideoQueryBuilder $query) => $query->latest())
            ->when($this->type === 'watching', fn (VideoQueryBuilder $query) => $query->watching());
    }
}
