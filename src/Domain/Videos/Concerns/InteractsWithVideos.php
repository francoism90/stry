<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait InteractsWithVideos
{
    /**
     * @return HasMany<Video, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class)->chaperone();
    }
}
