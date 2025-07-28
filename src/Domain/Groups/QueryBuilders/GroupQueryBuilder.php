<?php

declare(strict_types=1);

namespace Domain\Groups\QueryBuilders;

use Domain\Groups\Enums\GroupType;
use Illuminate\Database\Eloquent\Builder;

class GroupQueryBuilder extends Builder
{
    public function mixer(): self
    {
        return $this->where('type', GroupType::Mixer);
    }

    public function favorites(): self
    {
        return $this->where('type', GroupType::Favorite);
    }

    public function saves(): self
    {
        return $this->where('type', GroupType::Saved);
    }

    public function views(): self
    {
        return $this->where('type', GroupType::Viewed);
    }
}
