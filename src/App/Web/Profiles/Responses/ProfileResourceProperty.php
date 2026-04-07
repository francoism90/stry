<?php

declare(strict_types=1);

namespace App\Web\Profiles\Responses;

use App\Api\Profiles\Resources\ProfileResource;
use Domain\Profiles\Models\Profile;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class ProfileResourceProperty implements ProvidesInertiaProperty
{
    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?ProfileResource => $this->getResource($context));
    }

    protected function getResource(PropertyContext $context): ?ProfileResource
    {
        $profile = $context->request->attributes->get('currentProfile');

        if (! $profile instanceof Profile) {
            return null;
        }

        return $profile->toResource(ProfileResource::class);
    }
}
