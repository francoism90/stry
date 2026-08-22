<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Profiles\Models\Profile;
use Domain\Videos\Models\Video;

class GetRandomVideo
{
    /**
     * @param  array<int, int>  $excludeIds
     */
    public function handle(array $excludeIds = []): ?Video
    {
        return Video::query()
            ->verified()
            ->forProfile(Profile::current())
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->first();
    }
}
