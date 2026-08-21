<?php

declare(strict_types=1);

namespace Domain\Transcodes\Filters;

use Domain\Transcodes\Enums\TranscodeScope;
use Foxws\ScoutBuilder\Filters\Filter;
use Laravel\Scout\Builder;

class TranscodeScopeFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (! is_string($value)) {
            return;
        }

        $scope = TranscodeScope::tryFrom($value);

        if (! $scope instanceof TranscodeScope || $scope === TranscodeScope::All) {
            return;
        }

        $query->where('state', $scope->value);
    }
}
