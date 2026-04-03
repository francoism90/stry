<?php

declare(strict_types=1);

namespace App\Web\Profiles\Responses;

use Domain\Users\Models\User;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class ProfileCollectionProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected User $user,
        protected string $currentProfile = '',
    ) {}

    public function toInertiaProperty(PropertyContext $context): array
    {
        return once(fn (): array => $this->getProfiles());
    }

    protected function getProfiles(): array
    {
        return $this->user
            ->profiles()
            ->ordered()
            ->get()
            ->toSwitcherArray($this->currentProfile);
    }
}
