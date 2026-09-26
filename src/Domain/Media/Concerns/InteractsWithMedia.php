<?php

declare(strict_types=1);

namespace Domain\Media\Concerns;

use Domain\Media\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\InteractsWithMedia as HasMedia;

trait InteractsWithMedia
{
    /** @use HasMedia<Media> */
    use HasMedia;

    /**
     * @return MorphMany<Media, $this>
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->chaperone();
    }
}
