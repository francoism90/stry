<?php

declare(strict_types=1);

namespace Domain\Videos\Scopes;

use Domain\Groups\Enums\GroupType;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class VideoFilterScope
{
    public function __construct(
        protected GroupType|string|null $type = null,
    ) {}

    public function __invoke(VideoQueryBuilder $query): void
    {
        $query
            ->verified()
            ->with(['media', 'playlists', 'tags'])
            ->unless($this->type, fn (VideoQueryBuilder $query) => $query->inRandomOrder())
            ->when($this->type === 'newest', fn (VideoQueryBuilder $query) => $query->latest())
            ->when($this->type === 'watching', fn (VideoQueryBuilder $query) => $query->watching());
    }
}
