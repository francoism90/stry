<?php

declare(strict_types=1);

namespace App\Api\Users\Broadcasting;

use Domain\Users\Models\User;

class UserChannel
{
    public function join(User $user, User $model): bool
    {
        return $user->can('update', $model);
    }
}
