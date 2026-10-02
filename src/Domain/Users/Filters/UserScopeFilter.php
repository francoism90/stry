<?php

declare(strict_types=1);

namespace Domain\Users\Filters;

use Domain\Users\Enums\UserScope;
use Foxws\ScoutBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

class UserScopeFilter implements Filter
{
    /**
     * @param  Builder<Model>  $query
     */
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        match (UserScope::tryFrom($value)) {
            UserScope::Verified => $query->where('email_verified_at', '>', 0),
            UserScope::Unverified => $query->where('email_verified_at', 0),
            UserScope::Deleted => $query->onlyTrashed(),
            default => null,
        };
    }
}
