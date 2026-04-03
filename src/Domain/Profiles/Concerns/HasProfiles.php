<?php

declare(strict_types=1);

namespace Domain\Profiles\Concerns;

use Domain\Profiles\Models\Profile;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasProfiles
{
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class)->chaperone();
    }

    public function currentProfile(): ?Profile
    {
        return $this
            ->profiles()
            ->current()
            ->first();
    }
}
