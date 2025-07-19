<?php

declare(strict_types=1);

namespace Domain\Groups\QueryBuilders;

use Domain\Groups\Enums\GroupSet;
use Domain\Groups\Enums\GroupType;
use Illuminate\Database\Eloquent\Builder;

class GroupQueryBuilder extends Builder
{
    public function published(): self
    {
        return $this->whereDate('published_at', '<=', now());
    }

    public function mixer(): self
    {
        return $this->where('type', GroupType::Mixer);
    }

    public function system(): self
    {
        return $this->where('type', GroupType::System);
    }

    public function personal(): self
    {
        return $this
            ->whereIn('type', [GroupType::Private, GroupType::Public]);
    }

    public function favorites(): self
    {
        return $this
            ->system()
            ->where('kind', GroupSet::Favorite);
    }

    public function saves(): self
    {
        return $this
            ->system()
            ->where('kind', GroupSet::Saved);
    }

    public function views(): self
    {
        return $this
            ->system()
            ->where('kind', GroupSet::Viewed);
    }
}
