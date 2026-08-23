<?php

declare(strict_types=1);

namespace Domain\Groups\Scopes;

use Domain\Groups\Enums\GroupType;
use Laravel\Scout\Builder;

readonly class GroupTypeScope
{
    public function __invoke(Builder $scout): void
    {
        $scout
            ->whereNotIn('type', [GroupType::Mixer->value])
            ->orderBy('type_priority');
    }
}
