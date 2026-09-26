<?php

declare(strict_types=1);

namespace Domain\Groups\Concerns;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

trait HasGroups
{
    public static function bootHasGroups(): void
    {
        static::deleting(function (self $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model)) && ! $model->isForceDeleting()) {
                return;
            }

            $model->groups()->cursor()->each(fn (Group $group) => $group->delete());
        });
    }

    /**
     * @return HasMany<Group, $this>
     */
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

    /**
     * @return HasMany<Group, $this>
     */
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
                'has' => (bool) $group->getAttribute('modelable'),
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

    public function markInGroup(Video $video, GroupType $type, ?array $options = null): Video
    {
        return $video->attachToGroup($this->groupFor($type), $options);
    }

    public function toggleInGroup(Video $video, GroupType $type, ?array $options = null): Group
    {
        return $video->toggleGroup($this->groupFor($type), $options);
    }

    /**
     * Resolve the group types each of the given models belongs to using a single query.
     *
     * @template TModel of Model
     *
     * @param  Collection<int, TModel>  $models
     * @return Collection<array-key, Collection<int, GroupType>>
     */
    public function groupTypesFor(Collection $models): Collection
    {
        if ($models->isEmpty()) {
            return Collection::make();
        }

        return $this->groups()
            ->join('groupables', 'groupables.group_id', '=', 'groups.id')
            ->where('groupables.groupable_type', $models->first()->getMorphClass())
            ->whereIn('groupables.groupable_id', $models->map(fn (Model $model) => $model->getKey()))
            ->toBase()
            ->get(['groups.type', 'groupables.groupable_id'])
            ->groupBy('groupable_id')
            ->map(fn (Collection $rows) => $rows
                ->map(fn (object $row) => GroupType::from($row->type))
                ->unique()
                ->values()
            );
    }

    public function groupHasModel(Model $model, GroupType $type): bool
    {
        $group = $this->groups()->firstWhere('type', $type);

        return $group ? $group->hasGroupable($model) : false;
    }
}
