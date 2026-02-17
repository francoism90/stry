<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Broadcasting;

use Domain\Transcodes\Models\Transcode;
use Domain\Users\Models\User;

class TranscodeChannel
{
    public function join(User $user, Transcode $transcode): bool
    {
        return $user->can('view', $transcode);
    }
}
