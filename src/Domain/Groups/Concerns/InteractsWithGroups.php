<?php

declare(strict_types=1);

namespace Domain\Groups\Concerns;

use ArrayAccess;
use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Groups\Models\Groupable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait InteractsWithGroups
{
    public static function bootInteractsWithGroups(): void
    {
        static::deleting(function (Model $model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->groups()->detach();
        });
    }

    public function groups(): MorphToMany
    {
        return $this->morphToMany(Group::class, 'groupable')
            ->using(Groupable::class)
            ->withPivot(['group_id', 'options'])
            ->withTimestamps();
    }

    public function syncGroup(Group $group, ?array $options = null): static
    {
        return $this->syncGroups([$group], $options);
    }

    public function syncGroups(array|ArrayAccess|Collection $groups = [], ?array $options = null, bool $detach = false): static
    {
        $groups = static::convertToGroups($groups);

        $this->groups()->syncWithPivotValues(
            ids: $groups->pluck('id')->toArray(),
            values: ['options' => $options, 'updated_at' => now()],
            detaching: $detach,
        );

        return $this;
    }

    public function detachGroup(Group $group): static
    {
        return $this->detachGroups([$group]);
    }

    public function detachGroups(array|ArrayAccess|Collection $groups = []): static
    {
        $items = static::convertToGroups($groups);

        $items->each(fn (Group $group) => $this->groups()->detach($group));

        return $this;
    }

    public static function convertToGroups(array|ArrayAccess|Collection $values = []): Collection
    {
        return collect($values)
            ->map(fn (Group|int|string $value) => $value instanceof Group ? $value : Group::find($value))
            ->filter();
    }

    public function getGroup(?GroupType $type = null): ?Group
    {
        return $this
            ->groups()
            ->when($type, fn ($query) => $query->where('type', $type))
            ->first();
    }

    public function hasGroup(?GroupType $type = null): bool
    {
        return $this
            ->groups()
            ->when($type, fn ($query) => $query->where('type', $type))
            ->exists();
    }

    public function scopeWhereGroup(Builder $query, ?GroupType $type = null): Builder
    {
        return $query->whereHas('groups', fn ($query) => $query
            ->when($type, fn ($query) => $query->where('type', $type)),
        );
    }
}
