<?php

declare(strict_types=1);

namespace Domain\Profiles\QueryBuilders;

use Domain\Profiles\States;
use Illuminate\Database\Eloquent\Builder;

class ProfileQueryBuilder extends Builder
{
    public function enabled(): self
    {
        return $this->whereState('state', States\Enabled::class);
    }

    public function disabled(): self
    {
        return $this->whereState('state', States\Disabled::class);
    }

    public function ordered(): self
    {
        return $this
            ->orderByDesc('is_primary')
            ->orderBy('name');
    }

    public function current(): self
    {
        return $this
            ->enabled()
            ->ordered();
    }
}
