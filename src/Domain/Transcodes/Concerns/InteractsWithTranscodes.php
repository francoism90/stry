<?php

declare(strict_types=1);

namespace Domain\Transcodes\Concerns;

use Domain\Transcodes\Models\Transcode;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

trait InteractsWithTranscodes
{
    public static function bootInteractsWithTranscodes(): void
    {
        static::deleting(function (self $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model)) && ! $model->isForceDeleting()) {
                return;
            }

            $model->transcodes()->cursor()->each(fn (Transcode $transcode) => $transcode->delete());
        });
    }

    /**
     * @return MorphMany<Transcode, $this>
     */
    public function transcodes(): MorphMany
    {
        return $this->morphMany(Transcode::class, 'transcodable')->chaperone();
    }

    public function createTranscode(array $attributes = []): Transcode
    {
        return $this->transcodes()->create([
            'file_name' => 'video_av1.mp4',
            'disk' => Transcode::getDestinationDisk(),
            ...$attributes,
        ]);
    }

    public function hasTranscode(): bool
    {
        return $this
            ->transcodes()
            ->exists();
    }

    public function getTranscode(): ?Transcode
    {
        return $this
            ->transcodes()
            ->current()
            ->first();
    }
}
