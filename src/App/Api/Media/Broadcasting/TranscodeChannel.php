<?php

declare(strict_types=1);

namespace App\Api\Media\Broadcasting;

use Domain\Media\Models\Transcode;
use Domain\Users\Models\User;

class TranscodeChannel
{
    public function join(User $user, Transcode $transcode): bool
    {
        return $user->can('view', $transcode);
    }
}
