<?php

declare(strict_types=1);

namespace Domain\Media\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\InteractsWithMedia as HasMedia;

trait InteractsWithMedia
{
    use HasMedia;

    public function media(): MorphMany
    {
        return $this->morphMany($this->getMediaModel(), 'model')->chaperone();
    }
}
