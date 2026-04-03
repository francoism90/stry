<?php

declare(strict_types=1);

namespace App\Web\Profiles\Responses;

use App\Api\Profiles\Resources\ProfileResource;
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

        return $this->user
            ->loadMissing('profiles')
            ->toResource(ProfileResource::class);
    }

    protected function getCurrentProfile(): ?string
    {
        $currentProfile = Session::get('profiles.current') ?? ;

        if (Session::has('profiles.current')) {
            return Session::get('profiles.current');
        }

        // Get the current profile from the session
        $currentProfile = Profile::findFromUlid((string) $request->session()->get('profiles.current', ''));

        return $this->user?->profiles()->current()?->ulid;
    }
}
