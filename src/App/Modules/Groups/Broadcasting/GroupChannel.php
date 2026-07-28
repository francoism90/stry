<?php

declare(strict_types=1);

namespace App\Modules\Groups\Broadcasting;

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;

class GroupChannel
{
    public function join(User $user, Group $group): bool
    {
        return $user->can('view', $group);
    }
}
