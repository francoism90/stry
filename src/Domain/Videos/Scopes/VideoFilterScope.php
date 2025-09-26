<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoFilterScope
{
    public function __construct(
        protected readonly ?string $type = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->verified()
            ->with(['media', 'playlists', 'tags'])
            ->when(blank($this->type), fn (VideoQueryBuilder $query) => $query->inRandomOrder())
            ->when($this->type === 'newest', fn (VideoQueryBuilder $query) => $query->latest())
            ->when($this->type === 'watching', fn (VideoQueryBuilder $query) => $query->watching());
    }
}
