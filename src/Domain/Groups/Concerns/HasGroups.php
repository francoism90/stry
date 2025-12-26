<?php

declare(strict_types=1);

namespace Domain\Groups\Concerns;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function isFavorite(Model $model): bool
    {
        return $this->groupHasModel($model, GroupType::Favorited);
    }

    public function isSaved(Model $model): bool
    {
        return $this->groupHasModel($model, GroupType::Saved);
    }

    public function isViewed(Model $model): bool
    {
        return $this->groupHasModel($model, GroupType::Viewed);
    }

    public function customGroup(string $name): Group
    {
        return $this->groups()->firstOrCreate([
            'name' => $name,
            'type' => GroupType::Custom,
        ]);
    }

    public function favoritedGroup(): Group
    {
        return $this->findOrCreateGroup(GroupType::Favorited);
    }

    public function savedGroup(): Group
    {
        return $this->findOrCreateGroup(GroupType::Saved);
    }

    public function viewedGroup(): Group
    {
        return $this->findOrCreateGroup(GroupType::Viewed);
    }

    public function markAsFavorited(Model $model, ?array $options = null): Model
    {
        return $model->attachToGroup($this->favoritedGroup(), $options);
    }

    public function markAsSaved(Model $model, ?array $options = null): Model
    {
        return $model->attachToGroup($this->savedGroup(), $options);
    }

    public function markAsViewed(Model $model, ?array $options = null): Model
    {
        return $model->attachToGroup($this->viewedGroup(), $options);
    }

    public function toggleFavorited(Model $model, ?array $options = null): Model
    {
        return $model->toggleGroup($this->favoritedGroup(), $options);
    }

    public function toggleSaved(Model $model, ?array $options = null): Model
    {
        return $model->toggleGroup($this->savedGroup(), $options);
    }

    public function toggleViewed(Model $model, ?array $options = null): Model
    {
        return $model->toggleGroup($this->viewedGroup(), $options);
    }

    public function findOrCreateGroup(GroupType $type): Group
    {
        return $this->groups()->firstOrCreate([
            'type' => $type,
        ]);
    }

    public function groupHasModel(Model $model, GroupType $type): bool
    {
        $group = $this->groups()->where('type', $type)->first();

        return $group ? $group->hasGroupable($model) : false;
    }
}
