<?php

declare(strict_types=1);

namespace Domain\Groups\Scopes;

use Domain\Groups\Models\Group;
use Laravel\Scout\Builder;

readonly class GroupTypeScope
{
    /**
     * @param  Builder<Group>  $scout
     */
    public function __invoke(Builder $scout): void
    {
        $scout->orderBy('type_priority');
    }
}
