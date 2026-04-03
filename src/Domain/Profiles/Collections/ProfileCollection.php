<?php

declare(strict_types=1);

namespace Domain\Profiles\Collections;

use Domain\Profiles\Models\Profile;
use Illuminate\Database\Eloquent\Collection;

class ProfileCollection extends Collection
{
    public function toSwitcherArray(string $currentProfile = ''): array
    {
        return $this
            ->map(fn (Profile $profile): array => $profile->toSwitcherArray($currentProfile))
            ->values()
            ->all();
    }
}
