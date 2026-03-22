<?php

declare(strict_types=1);

namespace Domain\Groups\Concerns;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

trait HasGroups
{
    public static function bootHasGroups(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->groups()->cursor()->each(fn (Group $group) => $group->delete());
        });
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class)->chaperone();
    }

    public function findOrCreateGroup(GroupType $type, ?string $name = null, ?array $attributes = null): Group
    {
        $criteria = filled($name)
            ? ['name' => $name, 'type' => $type]
            : ['type' => $type];

        return $this->groups()->firstOrCreate($criteria, $attributes ?? []);
    }

    public function customGroups(): HasMany
    {
        return $this->groups()
            ->where('type', GroupType::Custom)
            ->orderBy('name');
    }

    /**
     * @return Collection<int, array{id: mixed, name: string, has: bool}>
     */
    public function customGroupsFor(Model $model): Collection
    {
        return $this->customGroups()
            ->forModel($model)
            ->get()
            ->map(fn (Group $group) => [
                'id' => $group->getRouteKey(),
                'name' => (string) $group->name,
                'has' => (bool) $group->modelable,
            ]);
    }

    public function isInGroup(Model $model, GroupType $type): bool
    {
        return $this->groupHasModel($model, $type);
    }

    public function groupFor(GroupType $type): Group
    {
        return $this->findOrCreateGroup(type: $type);
    }

    public function markInGroup(Model $model, GroupType $type, ?array $options = null): Model
    {
        return $model->attachToGroup($this->groupFor($type), $options);
    }

    public function toggleInGroup(Model $model, GroupType $type, ?array $options = null): Model
    {
        return $model->toggleGroup($this->groupFor($type), $options);
    }

    public function groupHasModel(Model $model, GroupType $type): bool
    {
        $group = $this->groups()->firstWhere('type', $type);

        return $group ? $group->hasGroupable($model) : false;
    }
}
