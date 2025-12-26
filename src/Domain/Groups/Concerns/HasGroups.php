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

    public function favorite(): Group
    {
        return $this->findOrCreateGroup(GroupType::Favorite);
    }

    public function saved(): Group
    {
        return $this->findOrCreateGroup(GroupType::Saved);
    }

    public function viewed(): Group
    {
        return $this->findOrCreateGroup(GroupType::Viewed);
    }

    public function markAsFavorite(Model $model, ?array $options = null): static
    {
        return $model->attachToGroup($this->favorite(), $options);
    }

    public function markAsSaved(Model $model, ?array $options = null): static
    {
        return $model->attachToGroup($this->saved(), $options);
    }

    public function markAsViewed(Model $model, ?array $options = null): static
    {
        return $model->attachToGroup($this->viewed(), $options);
    }

    public function toggleFavorite(Model $model, ?array $options = null): static
    {
        return $model->toggleGroup($this->favorite(), GroupType::Favorite, $options);
    }

    public function toggleSaved(Model $model, ?array $options = null): static
    {
        return $model->toggleGroup($this->saved(), GroupType::Saved, $options);
    }

    public function toggleViewed(Model $model, ?array $options = null): static
    {
        return $model->toggleGroup($this->viewed(), GroupType::Viewed, $options);
    }

    public function findOrCreateGroup(GroupType $type): Group
    {
        return $this->groups()->firstOrCreate([
            'type' => $type,
        ]);
    }
}
