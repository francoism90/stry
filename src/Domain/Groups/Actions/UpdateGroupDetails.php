<?php

declare(strict_types=1);

namespace Domain\Groups\Actions;

use Domain\Groups\Models\Group;
use Illuminate\Support\Arr;

class UpdateGroupDetails
{
    public function handle(Group $group, array $attributes = []): void
    {
        $group->updateOrFail(
            Arr::only($attributes, $group->getFillable()),
        );
    }
}
