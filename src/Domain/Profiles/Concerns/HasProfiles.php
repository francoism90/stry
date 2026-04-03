<?php

declare(strict_types=1);

namespace Domain\Profiles\Concerns;

use Domain\Profiles\Models\Profile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasProfiles
{
    public static function bootHasProfiles(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->profiles()->cursor()->each(fn (Profile $profile) => $profile->delete());
        });
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class)->chaperone();
    }

    public function currentProfile(): ?Profile
    {
        return $this->profiles()
            ->current()
            ->first();
    }
}
