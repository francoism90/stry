<?php

declare(strict_types=1);

namespace Domain\Profiles\Support;

use Domain\Profiles\Models\Profile;

class CurrentProfileContext
{
    protected ?Profile $profile = null;

    public function set(?Profile $profile): void
    {
        $this->profile = $profile;
    }

    public function forget(): void
    {
        $this->profile = null;
    }

    public function get(): ?Profile
    {
        return $this->profile;
    }
}
