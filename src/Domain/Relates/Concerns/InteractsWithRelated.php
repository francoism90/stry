<?php

declare(strict_types=1);

namespace Domain\Relates\Concerns;

use Domain\Relates\Models\Related;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait InteractsWithRelated
{
    public static function bootInteractsWithRelated(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->related()->cursor()->each(fn (Related $related) => $related->delete());
        });
    }

    public function related(): MorphMany
    {
        return $this->morphMany(Related::class, 'relatable')->chaperone();
    }
}
