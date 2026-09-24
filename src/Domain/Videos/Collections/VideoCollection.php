<?php

declare(strict_types=1);

namespace Domain\Videos\Collections;

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
     * Resolve the user's group types for every video using a single query.
     */
    public function loadGroupTypesFor(User $user): static
    {
        $groupTypes = $user->groupTypesFor($this->toBase());

        return $this->each(fn (Video $video) => $video->setGroupTypesFor(
            $user,
            $groupTypes->get($video->getKey(), BaseCollection::make()),
        ));
    }
}
