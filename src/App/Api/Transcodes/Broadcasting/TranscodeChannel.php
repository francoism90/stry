<?php

declare(strict_types=1);

namespace App\Api\Transcodes\Broadcasting;

use Domain\Transcodes\Models\Transcode;
use Domain\Users\Models\User;

class TranscodeChannel
{
    public function join(User $user, Transcode $playlist): bool
    {
        return $user->can('view', $playlist);
    }
}
