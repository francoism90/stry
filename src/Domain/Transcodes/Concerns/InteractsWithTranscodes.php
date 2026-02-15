<?php

declare(strict_types=1);

namespace Domain\Transcodes\Concerns;

use Domain\Transcodes\Models\Transcode;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait InteractsWithTranscodes
{
    public function transcodes(): HasMany
    {
        return $this->hasMany(Transcode::class);
    }
}
