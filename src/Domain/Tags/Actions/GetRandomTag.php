<?php

declare(strict_types=1);

namespace Domain\Tags\Actions;

use Domain\Profiles\Models\Profile;
use Domain\Tags\Models\Tag;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;

class GetRandomTag
{
    /**
     * @param  array<int, int>  $excludeIds
     */
    public function handle(array $excludeIds = []): ?Tag
    {
        $profile = Profile::current();

        return Tag::query()
            ->whereHas('videos', fn (VideoQueryBuilder $query) => $query->verified()->forProfile($profile))
            ->forProfile($profile)
            ->whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->first();
    }
}
