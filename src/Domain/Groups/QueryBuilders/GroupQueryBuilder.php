<?php

declare(strict_types=1);

namespace Domain\Groups\QueryBuilders;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Group
 *
 * @extends Builder<TModel>
 */
class GroupQueryBuilder extends Builder
{
    /** @return self<TModel> */
    public function custom(): self
    {
        return $this->where('type', GroupType::Custom);
    }

    /** @return self<TModel> */
    public function mixer(): self
    {
        return $this->where('type', GroupType::Mixer);
    }

    /** @return self<TModel> */
    public function liked(): self
    {
        return $this->where('type', GroupType::Liked);
    }

    /** @return self<TModel> */
    public function saved(): self
    {
        return $this->where('type', GroupType::Saved);
    }

    /** @return self<TModel> */
    public function viewed(): self
    {
        return $this->where('type', GroupType::Viewed);
    }

    /** @return self<TModel> */
    public function forModel(Model $model): self
    {
        return $this->withExists(['groupables as modelable' => fn (Builder $query) => $query
            ->where('groupable_type', $model->getMorphClass())
            ->where('groupable_id', $model->getKey()),
        ]);
    }
}
