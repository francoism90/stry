<?php

declare(strict_types=1);

namespace App\Web\Profiles\Responses;

use App\Api\Profiles\Resources\ProfileResource;
use Domain\Profiles\Models\Profile;
use Domain\Users\Models\User;
use Illuminate\Support\Facades\Session;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class ProfileResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?ProfileResource => $this->getResource());
    }

    protected function getResource(): ?ProfileResource
    {
        if (! $this->user) {
            return null;
        }

        $currentProfile = Session::has('profiles.current')
            ? Profile::findFromUlid(Session::get('profiles.current'))
            : $this->user->currentProfile();

        return $currentProfile?->toResource(ProfileResource::class);
    }
}
