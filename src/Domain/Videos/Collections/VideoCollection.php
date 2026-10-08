<?php

declare(strict_types=1);

namespace Domain\Videos\Collections;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * @extends Collection<int, Video>
 */
class VideoCollection extends Collection
{
    /**
     * Resolve the user's group types and watch positions for every video using a single query.
     */
    public function loadGroupTypesFor(User $user): static
    {
        $memberships = $user->groupMembershipsFor($this->toBase());

        return $this->each(function (Video $video) use ($user, $memberships): void {
            $rows = $memberships->get($video->getKey(), BaseCollection::make());

            $video->setGroupTypesFor($user, $rows
                ->map(fn (object $row) => GroupType::from($row->type))
                ->unique()
                ->values());

            $viewed = $rows->firstWhere('type', GroupType::Viewed->value);

            $video->setWatchTimeFor($user, (float) data_get(json_decode($viewed->options ?? '[]', true), 'time', 0));
        });
    }
}
