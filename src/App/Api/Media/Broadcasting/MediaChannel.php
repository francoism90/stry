<?php

declare(strict_types=1);

namespace App\Api\Media\Broadcasting;

use Domain\Media\Models\Media;
use Domain\Users\Models\User;

class MediaChannel
{
    public function join(User $user, Media $media): bool
    {
        return $user->can('view', $media);
    }
}
