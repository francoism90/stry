<?php

declare(strict_types=1);

namespace App\Api\Videos\Broadcasting;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

class VideoLibraryChannel
{
    public function join(User $user): bool
    {
        return $user->can('viewAny', Video::class);
    }
}
