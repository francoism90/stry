<?php

declare(strict_types=1);

namespace Domain\Groups\Concerns;

use Domain\Groups\Models\Group;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasGroups
{
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class)->chaperone();
    }
}
