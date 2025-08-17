<?php

declare(strict_types=1);

namespace Domain\Relates\Concerns;

use Domain\Relates\Models\Relatable;
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

            $model->relatables()->cursor()->each(fn (Relatable $relatable) => $relatable->delete());
        });
    }

    public function relatables(): MorphMany
    {
        return $this->morphMany(Relatable::class, 'relatable')->chaperone();
    }
}
