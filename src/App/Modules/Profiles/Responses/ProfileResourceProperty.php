<?php

declare(strict_types=1);

namespace App\Modules\Profiles\Responses;

use App\Modules\Profiles\Resources\ProfileResource;
use Domain\Profiles\Models\Profile;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

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
