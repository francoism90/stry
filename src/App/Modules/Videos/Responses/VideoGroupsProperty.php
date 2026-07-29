<?php

declare(strict_types=1);

namespace App\Modules\Videos\Responses;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Collection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoGroupsProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Video $video = null,
        protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        if (! $this->video || ! $this->user) {
            return Collection::empty();
        }

        return $this->user->customGroupsFor($this->video);
    }
}
