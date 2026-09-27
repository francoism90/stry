<?php

declare(strict_types=1);

namespace Domain\Profiles\Filters;

use Domain\Profiles\Enums\ProfileScope;
use Domain\Profiles\Models\Profile;
use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class ProfileScopeFilter implements Filter
{
    /**
     * @param  Builder<Profile>  $query
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        match (ProfileScope::tryFrom($value)) {
            ProfileScope::Kids => $query->where('is_kids', true),
            ProfileScope::Primary => $query->where('is_primary', true),
            default => null,
        };
    }
}
