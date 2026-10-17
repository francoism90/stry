<?php

declare(strict_types=1);

namespace Domain\Videos\Filters;

use Domain\Videos\Enums\VideoLibraryScope;
use Foxws\ScoutBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

class VideoLibraryScopeFilter implements Filter
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        match ($scope = VideoLibraryScope::tryFrom($value)) {
            null, VideoLibraryScope::All => null,
            VideoLibraryScope::Deleted => $query->onlyTrashed(),
            default => $query->where('state', $scope->value),
        };
    }
}
