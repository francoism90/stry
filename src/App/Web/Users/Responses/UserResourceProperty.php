<?php

declare(strict_types=1);

namespace App\Web\Users\Responses;

use App\Api\Users\Resources\UserResource;
use Domain\Users\Models\User;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class UserResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?UserResource => $this->getResource());
    }

    protected function getResource(): ?UserResource
    {
        if (! $this->user) {
            return null;
        }

        // Append necessary attributes for the edit form
        $appends = [
            'avatar',
            'email',
        ];

        return $this->user
            ->loadMissing('roles', 'permissions')
            ->append($appends)
            ->toResource(UserResource::class);
    }
}
