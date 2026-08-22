<?php

declare(strict_types=1);

namespace Domain\Videos\Filters;

use Domain\Videos\Enums\VideoLibraryScope;
use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class VideoLibraryScopeFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        $scope = VideoLibraryScope::tryFrom($value);

        if (! $scope instanceof VideoLibraryScope || $scope === VideoLibraryScope::All) {
            return;
        }

        $query->where('state', $scope->value);
    }
}
