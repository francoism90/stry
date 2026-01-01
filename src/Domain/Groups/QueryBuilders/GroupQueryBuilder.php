<?php

declare(strict_types=1);

namespace Domain\Groups\QueryBuilders;

use Domain\Groups\Enums\GroupType;
use Illuminate\Database\Eloquent\Builder;

class GroupQueryBuilder extends Builder
{
    public function custom(): self
    {
        return $this->where('type', GroupType::Custom);
    }

    public function mixer(): self
    {
        return $this->where('type', GroupType::Mixer);
    }

    public function liked(): self
    {
        return $this->where('type', GroupType::Liked);
    }

    public function saved(): self
    {
        return $this->where('type', GroupType::Saved);
    }

    public function viewed(): self
    {
        return $this->where('type', GroupType::Viewed);
    }
}
