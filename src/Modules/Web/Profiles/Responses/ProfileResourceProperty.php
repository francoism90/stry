<?php

declare(strict_types=1);

namespace Modules\Web\Profiles\Responses;

use Domain\Profiles\Models\Profile;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Modules\Api\Profiles\Resources\ProfileResource;

readonly class ProfileResourceProperty implements ProvidesInertiaProperty
{
    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?ProfileResource => $this->getResource());
    }

    protected function getResource(): ?ProfileResource
    {
        $profile = Profile::current();

        if (! $profile instanceof Profile) {
            return null;
        }

        return $profile->toResource(ProfileResource::class);
    }
}
