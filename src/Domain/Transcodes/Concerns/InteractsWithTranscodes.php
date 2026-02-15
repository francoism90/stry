<?php

declare(strict_types=1);

namespace Domain\Transcodes\Concerns;

use Domain\Transcodes\Models\Transcode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait InteractsWithTranscodes
{
    public static function bootInteractsWithTranscodes(): void
    {
        static::deleting(function (Model $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->transcodes()->cursor()->each(fn (Transcode $transcode) => $transcode->delete());
        });
    }

    public function transcodes(): HasMany
    {
        return $this->hasMany(Transcode::class);
    }

    public function createTranscode(array $attributes = []): Transcode
    {
        return $this->transcodes()->create([
            ...$attributes,
        ]);
    }

    public function hasTranscode(): bool
    {
        return $this->transcodes()->active()->exists();
    }

    public function getTranscode(): ?Transcode
    {
        return $this->transcodes()->active()->first();
    }
}
