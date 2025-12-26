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

    public function customGroup(string $name): Group
    {
        return $this->groups()->firstOrCreate([
            'name' => $name,
            'type' => GroupType::Custom,
        ]);
    }

    public function likedGroup(): Group
    {
        return $this->findOrCreateGroup(GroupType::Liked);
    }

    public function savedGroup(): Group
    {
        return $this->findOrCreateGroup(GroupType::Saved);
    }

    public function viewedGroup(): Group
    {
        return $this->findOrCreateGroup(GroupType::Viewed);
    }

    public function markAsLiked(Model $model, ?array $options = null): static
    {
        return $model->attachToGroup($this->likedGroup(), $options);
    }

    public function markAsSaved(Model $model, ?array $options = null): static
    {
        return $model->attachToGroup($this->savedGroup(), $options);
    }

    public function markAsViewed(Model $model, ?array $options = null): static
    {
        return $model->attachToGroup($this->viewedGroup(), $options);
    }

    public function toggleLiked(Model $model, ?array $options = null): static
    {
        return $model->toggleGroup($this->likedGroup(), GroupType::Liked, $options);
    }

    public function toggleSaved(Model $model, ?array $options = null): static
    {
        return $model->toggleGroup($this->savedGroup(), GroupType::Saved, $options);
    }

    public function toggleViewed(Model $model, ?array $options = null): static
    {
        return $model->toggleGroup($this->viewedGroup(), GroupType::Viewed, $options);
    }

    public function findOrCreateGroup(GroupType $type): Group
    {
        return $this->groups()->firstOrCreate([
            'type' => $type,
        ]);
    }
}
