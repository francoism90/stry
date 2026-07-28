<?php

declare(strict_types=1);

namespace App\Modules\Profiles\Broadcasting;

use Domain\Profiles\Models\Profile;
use Domain\Users\Models\User;

class ProfileChannel
{
    public function join(User $user, Profile $profile): bool
    {
        return $user->can('view', $profile);
    }
}
