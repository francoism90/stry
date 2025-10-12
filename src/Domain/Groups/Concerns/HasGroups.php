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

    public function findOrCreateGroup(GroupType $type): Group
    {
        return $this->groups()->firstOrCreate([
            'type' => $type,
        ]);
    }
}
