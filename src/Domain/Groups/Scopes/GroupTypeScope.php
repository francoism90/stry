<?php

declare(strict_types=1);

namespace Domain\Groups\Scopes;

use Laravel\Scout\Builder;

readonly class GroupTypeScope
{
    public function __invoke(Builder $scout): void
    {
        $scout->orderBy('type_priority');
    }
}
