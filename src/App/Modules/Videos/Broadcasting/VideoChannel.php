<?php

declare(strict_types=1);

namespace App\Api\Videos\Broadcasting;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;

class VideoChannel
{
    public function join(User $user, Video $video): bool
    {
        return $user->can('view', $video);
    }
}
