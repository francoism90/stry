<?php

declare(strict_types=1);

namespace App\Api\Tags\Broadcasting;

use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;

class TagChannel
{
    public function join(User $user, Tag $tag): bool
    {
        return $user->can('view', $tag);
    }
}
